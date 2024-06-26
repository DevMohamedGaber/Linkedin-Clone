<?php
namespace API\Core;

use \API\Core\Middleware;

class Router
{
  // routes cache
  private $routes = [];

  // method types (only needed ones)
  public function get($uri, $controllerName)
  {
    return $this->AddRoute($uri, $controllerName, 'GET');
  }
  public function post($uri, $controllerName, $params = [])
  {
    return $this->AddRoute($uri, $controllerName, 'POST', $params);
  }
  public function delete($uri, $controllerName)
  {
    return $this->AddRoute($uri, $controllerName, 'DELETE');
  }

  public function only($middleware)
  {
    $this->routes[array_key_last($this->routes)]['middleware'] = $middleware;
  }
  // handle and validate incoming requests
  public function HandleRequest($uri, $method, $params)
  {
    if(count($this->routes) == 0)
    {
      abort();
    }

    $uri = parse_url($uri);
    $path = $uri["path"];
    $pathContent = explode('/', substr($path, 1));
    $route = null;

    if(count($pathContent) > 1)
    {
      $pathContentCount = count($pathContent);
      
      foreach ($this->routes as $routeItem)
      {
        // skip the ones with diffrent method type from the start
        if($routeItem['method'] != $method) {
          continue;
        }
        
        $checkedPath = "";
        
        for($i = 0; $i < $pathContentCount; $i++)
        {
          // if already fount the route, add to parametars
          if($route != null) 
          {
            $params[] = $pathContent[$i];
            continue;
          }
          
          $checkedPath = $checkedPath . '/' . $pathContent[$i];
          
          if($checkedPath != $routeItem['uri'] || count($routeItem['params']) != $pathContentCount - $i - 1)
          {
            continue;
          }
          $route = $routeItem;
        }
        if($route != null) 
        {
          break;
        }
      }
    }
    else
    {
      foreach ($this->routes as $routeItem)
      {
        if($path == $routeItem['uri'] && $method == $routeItem['method'] && count($routeItem['params']) == count($params)) {
          $route = $routeItem;
          break;
        }
      }
    }

    if($route == null)
    {
      return abort();
    }

    if($route['middleware'] != null)
    {
      Middleware::Resolve($route['middleware']);
    }

    require base_path($route['controllerPath']);
    
    $controller = new $route['controllerName'];
    call_user_func_array(array($controller, $route['action']), $params);
  }

  // helpers
  private function AddRoute($uri, $controller, $method, $params = [])
  {
    $controllerName = "";
    $actionName = "";
    // separate controller name and function name
    if(str_contains($controller, ':'))
    {
      $controllerArray = explode(':', $controller);
      $controllerName = $controllerArray[0];
      $actionName = $controllerArray[1];
    }
    else
    {
      $controllerName = $controller;
      $actionName = 'Index';
    }

    // extract parametars if any
    if(str_contains($uri, '{'))
    {
      $paramArray = explode('{', $uri);
      $uri = rtrim($paramArray[0], '/');
      
      for($i = 1; $i < count($paramArray); $i++)
      {
        $paramArray[$i] = rtrim($paramArray[$i], '/}');
        $params[] = $paramArray[$i];
      }
    }

    // cache the new route
    $this->routes[] = [
      'method' => $method,
      'uri' => $uri,
      'controllerName' => "\API\Controllers\\{$controllerName}Controller",
      'controllerPath' => "\api\controllers/{$controllerName}Controller.php",
      'action' => $actionName,
      'params' => $params,
      'middleware' => null
    ];

    return $this;
  }
}