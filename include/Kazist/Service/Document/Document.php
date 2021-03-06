<?php

/*
 * This file is part of Kazist Framework.
 * (c) Dedan Irungu <irungudedan@gmail.com>
 *  For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 * 
 */

namespace Kazist\Service\Document;

defined('KAZIST') or exit('Not Kazist Framework');

use Kazist\KazistFactory;
use Kazist\Service\Database\Query;

class Document {

    public $container = '';
    public $request = '';

    public function __construct($container, $request) {
        $this->container = $container;
        $this->request = $request;
    }

    public function getDocument() {

        $factory = new KazistFactory();
        $ignore_route = array('home', 'admin.home', 'login', 'logout');

        $router = $this->request->attributes->get('_route');
        $controller = $this->request->attributes->get('_controller');
        $params = $this->request->attributes->get('_route_params');
        $controller_arr = explode('Controllers', $controller);

        $document = $this->prepareDocument();

        $document->subset = $this->prepareSubset($document);
        $document->extension = $this->prepareExtension($document);
        // $document->permissions = $this->preparePermission($document);

        $document->main_route = $this->request->getRequestUri();
        $all_query = $this->request->query->all();

        unset($all_query['page']);

        $document->web_root = WEB_ROOT;
        $document->web_base = WEB_BASE;
        $document->main_route = $factory->generateUrl($router, $all_query, 0);
        $document->main_route_join = (strpos($document->main_route, '?')) ? '&' : '?';

        $document->current_url = $factory->generateUrl($router, $params, 0);
        $document->current_route = $router;
        $document->sub_route = str_replace('admin.', '', $router);
        $document->class = str_replace('\\', '/', $controller_arr[0]);

        $document->root_route = (in_array($router, $ignore_route)) ? $router : $this->formatBaseRoute($document->class);
        $document->base_route = (WEB_IS_ADMIN) ? 'admin.' . $document->root_route : $document->root_route;

        $document->user = $this->getUser();

        $this->setTimeZone($document);
        $this->setPageDetail($document);
        $this->setSearchCriteria($document);
        //  print_r($document); exit;
        return $document;
    }

    public function setTimeZone($document) {

        $timezone = 'Africa/Nairobi';

        if ($this->container->hasParameter('system.timezone')) {
            $timezone = $this->container->getParameter('system.timezone');
        }

        $document->timezone = $timezone;
    }

    public function getUser() {

        $doctrine = $this->container->get('doctrine');

        $factory = new KazistFactory();
        $temp_user = $factory->getUser();

        try {
            $new_user = $factory->getRecord('#__users_users', 'uu', array('uu.id=:id'), array('id' => $temp_user->id));
        } catch (\Exception $ex) {
            $doctrine->refresh = true;
            $doctrine->entity_path = JPATH_ROOT . 'applications/Users/Users/Code/Tables';

            if (is_dir($doctrine->entity_path)) {
                $doctrine->getEntityManager();
            }

            $new_user = $factory->getRecord('#__users_users', 'uu', array('uu.id=:id'), array('id' => $temp_user->id));
        }

        $user = (object) array_merge((array) $temp_user, (array) $new_user);

        return $user;
    }

    public function setSearchCriteria($document) {

        $clear = $this->request->get('clear');
        $search = $this->request->get('search');
        $session = $this->container->get('session');
        $router = $this->request->attributes->get('_route');

        $url_queries = $this->request->query->all();

        foreach ($url_queries as $key => $url_query) {
            if (!isset($search[$key]) && $key <> 'page') {
                $search[$key] = $url_query;
            }
        }

        if (!empty($search)) {
            $session->set($router . '.search', $search);
        } else {
            $search = $session->get($router . '.search');
        }

        if ($clear <> '') {
            $document->search = '';
            $session->remove($router . '.search');
        } else {
            $document->search = $search;
        }
    }

    public function setPageDetail($document) {

        $page = $this->request->query->get('page', '0');
        $session = $this->container->get('session');
        $router = $this->request->attributes->get('_route');

        $document->page = $page;
        $document->request = $this->request;
        $document->offset = ($session->get($router . '.offset')) ? $session->get($router . '.offset') : 0;
        $document->limit = ($session->get($router . '.limit')) ? $session->get($router . '.limit') : $this->container->getParameter('system.list_limit');


        $request_limit = $this->request->request->get('list_limit');

        if ($request_limit) {
            $document->limit = $request_limit;
        }

        if (!$document->limit) {
            $document->limit = 10;
        }

        $document->offset = $page * $document->limit;

        $session->set($router . '.limit', $document->limit);
        $session->set($router . '.offset', $document->offset);
    }

    public function prepareDocument() {

        $router = $this->request->attributes->get('_route');
        $controller = $this->request->attributes->get('_controller');
        $controller_arr = explode('Code', $controller);
        $extension_path = rtrim(str_replace('\\', '/', $controller_arr[0]), '/');

        $route_path = JPATH_ROOT . 'applications/' . $extension_path . '/Code/route.json';

        if (file_exists($route_path)) {

            $route_list = (json_decode(file_get_contents($route_path)));

            $front_routes = (isset($route_list->frontend) && !empty($route_list->frontend)) ? $route_list->frontend : array();
            $back_routes = (isset($route_list->backend) && !empty($route_list->backend)) ? $route_list->backend : array();

            $routes = array_merge($front_routes, $back_routes);

            foreach ($routes as $key => $route) {
                if ($controller == $route->controller) {
                    $route->extension_path = $extension_path;
                    return $route;
                }
            }
        }

        $route->extension_path = ( $route->extension_path <> '') ? $route->extension_path : $extension_path;

        return $route;
    }

    public function prepareSubset($document) {
        //Get Subset
        $query = new Query();

        $query->from('#__system_subsets', 'r');
        $query->select('r.id, r.name, r.title, r.version, r.has_view, r.explanation, r.description, r.extension_id');
        $query->where('r.id=:subset_id');
        $query->setParameter('subset_id', $document->subset_id);

        return $query->loadObject();
    }

    public function prepareExtension($document) {
        //Get Extension
        $query = new Query();
        $query->from('#__system_extensions', 'r');
        $query->select('r.name, r.title, r.path, r.icon, r.description, r.extension, r.version, r.is_core');
        $query->where('r.id=:id');
        $query->setParameter('id', $document->subset->extension_id);

        return $query->loadObject();
    }

    public function preparePermission($document) {
        // Get Permission
        $query = new Query();

        $tmp_permission_arr = array();

        $query->from('#__users_permission', 'p');
        $query->select('r.alias, p.can_add, p.can_view, p.can_write, p.can_delete, p.can_viewown, p.can_writeown, p.can_deleteown');
        $query->where('p.route_id=:route_id');
        $query->leftjoin('p', '#__users_roles', 'r', 'p.role_id=r.id');
        $query->setParameter('route_id', $document->id);
        $permissions = $query->fetchAll();

        if (!empty($permissions)) {
            foreach ($permissions as $key => $permission) {

                $alias = 'ROLE_' . $permission['alias'];


                foreach ($permission as $key => $value) {

                    if ($key <> 'alias' && $value) {
                        $tmp_permission_arr[$alias][$key] = $value;
                    }
                }

                $document->roles[] = $alias;
            }
        }

        return $tmp_permission_arr;
    }

    private function formatBaseRoute($route) {

        $val = str_replace('/', '.', $route);
        $val = trim(str_replace('Code', '', $val), '.');
        $val = strtolower($val);

        return $val;
    }

}
