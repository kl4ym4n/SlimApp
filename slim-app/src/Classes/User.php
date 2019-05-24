<?php
namespace SlimApp\Classes;

use Psr\Container\ContainerInterface;
use Valitron\Validator;

class User {
    protected $container;
    protected $db;
    protected $view;
    protected $router;

    public function __construct(ContainerInterface $container){
        $this->container = $container;
        $this->db = $this->container->get('db');
        $this->view = $this->container->get('view');
        $this->router = $this->container->get('router');
    }

    public function showLoginPage($request, $response, $args) {
        return $this->view->render($response, 'login.html', $args);
    }

    public function login($request, $response, $args) {
        $data = $request->getParsedBody();
        $validator = $this->getLoginValidator($data);
        $messages = [];
        if (!$validator->validate()) {
            foreach ($validator->errors() as $fieldName => $errors) {
                $messages[] = current($errors);
            }
            return $this->view->render($response, 'login.html', ['messages' => $messages,
                 'form_data' => $data
            ]);
        }

        $stmt = $this->db->prepare("SELECT * FROM users WHERE Email=:login OR Phone=:login");
        try {
            $stmt->bindValue('login', $data['username']);
            $stmt->execute();
            $user = $stmt->fetch();

            if ($user) {
                if(password_verify($data['password'], $user["Password"])) {
                    $_SESSION["user_id"] = $user['ID'];
                    return $response->withHeader('Location', $this->router->pathFor('show.dashboard'));
                } else {
                    $messages[] = $user['Email'];
                    $messages[] = 'Invalid Password';
                }
            } else {
                $messages[] = 'Invalid Email or Phone';
            }

        } catch (\PDOException $e){
            http_response_code(500);
            die('ERROR: ' . $e->getMessage());
        }
        return $this->view->render($response, 'login.html', ['messages' => $messages,
            'form_data' => $data
        ]);
    }

    public function showRegisterPage($request, $response, $args) {
        return $this->view->render($response, 'register.html', $args);
    }

    function customUUID($length){
        $pieces = [];
        $keyspace = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $max = mb_strlen($keyspace, '8bit') - 1;
        for ($i = 0; $i < $length; ++$i) {
            $pieces[] = $keyspace[random_int(0, $max)];
        }
        return implode('', $pieces);
    }

    public function register($request, $response, $args) {
        $data = $request->getParsedBody();

        $validator = $this->getRegistrationValidator($data);
        if (!$validator->validate()) {
            $messages = [];
            foreach ($validator->errors() as $fieldName => $errors) {
                $messages[] = current($errors);
            }
            return $this->view->render($response, 'register.html', ['messages' => $messages,
                'form_data' => $data
                ]);
        }
        else {
            $stmt = $this->db->prepare("INSERT INTO users VALUES (:ID, :Name, :Phone, :Email, :Password)");
            try {
                $user_id = $this->customUUID(15);
                $stmt->bindParam("ID", $user_id);
                $stmt->bindParam("Phone", $data['mobile_phone']);
                $stmt->bindParam("Email", $data['email']);
                $stmt->bindParam("Name", $data['first_name']);
                $pass = password_hash($data['password'], PASSWORD_DEFAULT);
                $stmt->bindParam("Password", $pass);
                $stmt->execute();
                $_SESSION["user_id"] = $user_id;
            } catch (\PDOException $e){
                http_response_code(500);
                die('ERROR: ' . $e->getMessage());
            }
        }
        return $response->withHeader('Location', $this->router->pathFor('show.dashboard'));
    }

    public function logout($request, $response, $args) {
        session_destroy();
        return $response->withHeader('Location', $this->router->pathFor('show.login'));
    }



    public function getRegistrationValidator($data)
    {
        Validator::addRule('unique' , function ($field, $value, array $params, array $fields) {
            if ($field === 'email' || $field === 'mobile_phone') {
                if ($field === 'email') {
                    $stmt = $this->db->prepare("SELECT * FROM users WHERE Email=:email");
                    $query_params = array('email' => $value);
                } else {
                    $stmt = $this->db->prepare("SELECT * FROM users WHERE Phone=:mobile_phone");
                    $query_params = array('mobile_phone' => $value);
                }
                try {
                    $stmt->execute($query_params);
                    $records = $stmt->fetchAll();
                    if (count($records) > 0) {
                        return false;
                    }
                } catch (\PDOException $e){
                    http_response_code(500);
                    die('ERROR: ' . $e->getMessage());
                }
            }
            return true;
        }, 'already exists');

        $validator = new Validator($data);
        $validator->rule('required', 'first_name');
        $validator->rule('lengthBetween', 'first_name', 1, 255);
        $validator->rule('required', 'mobile_phone');
        $validator->rule('unique', 'mobile_phone');
        $validator->rule('lengthBetween', 'mobile_phone', 1, 255);
        $validator->rule('required', 'email');
        $validator->rule('email', 'email');
        $validator->rule('unique', 'email');
        $validator->rule('required', 'password');
        $validator->rule('required', 'password_confirmation');
        $validator->rule('equals', 'password', 'password_confirmation');
        $validator->labels([
            'first_name' => 'First Name',
            'mobile_phone' => 'Mobile Phone',
            'email' => 'Email',
            'password' => 'Password',
            'password_confirmation' => 'Password Confirmation'
        ]);

        return $validator;
    }

    public function getloginValidator($data) {
        $validator = new Validator($data);
        $validator->rule('required', 'username');
        $validator->rule('required', 'password');
        $validator->labels([
            'username' => 'Email or Phone',
            'password' => 'Password',
        ]);

        return $validator;
    }
}