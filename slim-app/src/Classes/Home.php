<?php
namespace SlimApp\Classes;

use Psr\Container\ContainerInterface;

class Home {
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

    public function index($request, $response, $args) {
        return $this->view->render($response, 'index.html', $args);
    }

    public function aboutUs($request, $response, $args) {
        return $this->view->render($response, 'about.html', $args);
    }

    public function dashboard($request, $response, $args) {
        return $this->view->render($response, 'admin_dashboard.html', $args);
    }

}