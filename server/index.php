<?php

// ── Headers CORS (equivalente al .htaccess actual) ──────────────────────────
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}


require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/core/Response.php';

$database = new Database();
$db       = $database->getConnection();

// ── Autoload de core y controllers ──────────────────────────────────────────
require_once __DIR__ . '/core/Response.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/ClientesController.php';
require_once __DIR__ . '/controllers/ComprasController.php';
require_once __DIR__ . '/controllers/TiposUsuariosController.php';
require_once __DIR__ . '/controllers/InvernaderosController.php';
require_once __DIR__ . '/controllers/ProductosController.php';
require_once __DIR__ . '/controllers/StocksController.php';
require_once __DIR__ . '/controllers/PedidosController.php';
require_once __DIR__ . '/controllers/ProductosPedidosController.php';
require_once __DIR__ . '/controllers/ProveedoresController.php';
require_once __DIR__ . '/controllers/CotizacionesController.php';


$uri       = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$base      = '/FerreInver/server/';
$ruta      = str_replace($base, '', $uri);
$ruta      = trim($ruta, '/');
$segmentos = $ruta ? explode('/', $ruta) : [];

$recurso    = $segmentos[0] ?? null;   
$subrecurso = $segmentos[1] ?? null;   

$method = $_SERVER['REQUEST_METHOD'];

// ── Enrutamiento ─────────────────────────────────────────────────────────────
switch ($recurso) {

    case 'auth':
        $ctrl = new AuthController();
        if ($method === 'POST' && $subrecurso === 'login') {
            $ctrl->login();
        } else {
            Response::error("Ruta de autenticación no válida.", 404);
        }
        break;

    case 'clientes':
        $ctrl = new ClientesController();
        switch ($method) {
            case 'GET':
                $ctrl->index($subrecurso);
                break;
            case 'POST':
                $ctrl->create();
                break;
            case 'PUT':
                $ctrl->update($_GET['documento'] ?? null);
                break;
            case 'PATCH':
                if ($subrecurso === 'nombre') {
                    $ctrl->updateNombre($_GET['documento'] ?? null);
                } else {
                    Response::error("Subrecurso no válido.", 404);
                }
                break;
            case 'DELETE':
                $ctrl->deactivate($_GET['documento'] ?? null);
                break;
            default:
                Response::error("Método no permitido.", 405);
        }
        break;

    case 'compras':
        $ctrl = new ComprasController();
        switch ($method) {
            case 'GET':
                $ctrl->index($subrecurso);
                break;
            case 'POST':
                $ctrl->create();
                break;
            case 'PUT':
                $ctrl->update($_GET['id'] ?? null);
                break;
            case 'DELETE':
                $ctrl->delete($_GET['id'] ?? null);
                break;
            default:
                Response::error("Método no permitido.", 405);
        }
        break;

    case 'tipos-usuarios':
        $ctrl = new TiposUsuariosController();
        switch ($method) {
            case 'GET':
                $ctrl->index();
                break;
            case 'POST':
                $ctrl->create();
                break;
            case 'PUT':
                $ctrl->update($_GET['id'] ?? null);
                break;
            case 'DELETE':
                $ctrl->delete($_GET['id'] ?? null);
                break;
            default:
                Response::error("Método no permitido.", 405);
        }
        break;

    case 'invernaderos':
    $ctrl = new InvernaderosController();
    switch ($method) {
        case 'GET':    
            $ctrl->index();
                break;
        case 'POST':   
            $ctrl->create();                         
                break;
        case 'PUT':    
            $ctrl->update($_GET['id'] ?? null);      
            break;
        case 'DELETE':
            $ctrl->deactivate($_GET['id'] ?? null);  
            break;
        default:       Response::error("Método no permitido.", 405);
    }
    break;

    case 'productos':
    $ctrl = new ProductosController();
    $method_override = $_GET['_method'] ?? null;
    switch ($method) {
        case 'GET':    
            $ctrl->index();                                          
            break;
        case 'POST':
            if ($method_override === 'PUT')
                $ctrl->update($_GET['id'] ?? null);
            else
                $ctrl->create();
            break;
        case 'DELETE': 
            $ctrl->deactivate($_GET['id'] ?? null);                  
            break;
        default:       Response::error("Método no permitido.", 405);
    }
    break;

    case 'stocks':
    $ctrl = new StocksController();
    switch ($method) {
        case 'GET':    $ctrl->index();                          break;
        case 'POST':   $ctrl->create();                         break;
        case 'PUT':    $ctrl->update($_GET['id'] ?? null);      break;
        case 'DELETE': $ctrl->delete($_GET['id'] ?? null);      break;
        default:       Response::error("Método no permitido.", 405);
    }
    break;

    case 'pedidos':
    $ctrl = new PedidosController();
    // subrecurso 'completo' → POST /pedidos/completo
    if ($method === 'POST' && $subrecurso === 'completo') {
        $ctrl->createCompleto();
        break;
    }
    switch ($method) {
        case 'GET':    $ctrl->index();                          break;
        case 'POST':   $ctrl->create();                         break;
        case 'PUT':    $ctrl->update($_GET['id'] ?? null);      break;
        case 'DELETE': $ctrl->cancel($_GET['id'] ?? null);      break;
        default:       Response::error("Método no permitido.", 405);
    }
    break;

    case 'productos-pedidos':
    $ctrl = new ProductosPedidosController();
    switch ($method) {
        case 'GET':    
            $ctrl->index();                          
            break;
        case 'POST':   
            $ctrl->create();                         
            break;
        case 'PUT':    
            $ctrl->update($_GET['id'] ?? null);      
            break;
        case 'DELETE': 
            $ctrl->delete($_GET['id'] ?? null);      
            break;
        default:       Response::error("Método no permitido.", 405);
    }
    break;
        
    case 'cotizaciones':
        $ctrl = new CotizacionesController($db);
        $id   = isset($subrecurso) && is_numeric($subrecurso) ? (int)$subrecurso : null;
        switch ($method) {
            case 'GET':
                $ctrl->index();
                break;
            case 'POST':
                $ctrl->store();
                break;
            case 'PUT':
                if (!$id) { Response::error("ID requerido.", 400); break; }
                $ctrl->update($id);
                break;
            case 'DELETE':
                if (!$id) { Response::error("ID requerido.", 400); break; }
                $ctrl->destroy($id);
                break;
            default:
                Response::error("Método no permitido.", 405);
        }
        break;

    case 'proveedores':
    $ctrl = new ProveedoresController($db);
    switch ($method) {
        case 'GET':    $ctrl->index();                             break;
        case 'POST':   $ctrl->create();                            break;
        case 'PUT':    $ctrl->update($_GET['nit'] ?? null);        break;
        case 'DELETE': $ctrl->deactivate($_GET['nit'] ?? null);    break;
        default:       Response::error("Método no permitido.", 405);
    }
    break;
    
    
    default:
        Response::error("Recurso no encontrado.", 404);
}