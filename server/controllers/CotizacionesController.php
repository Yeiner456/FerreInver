<?php


require_once __DIR__ . '/../models/CotizacionesModel.php';
require_once __DIR__ . '/../core/Response.php';

class CotizacionesController
{

    private $model;

    public function __construct($db)
    {
        $this->model = new CotizacionesModel($db);
    }

    private function validarCampos(array $b): ?string
    {
        $requeridos = [
            'cliente_id',
            'invernadero_id',
            'largo',
            'ancho',
            'metros_cuadrados',
            'valor_m2',
            'total',
            'estado'
        ];

        foreach ($requeridos as $campo) {
            if (!isset($b[$campo]) || $b[$campo] === '' || $b[$campo] === null) {
                return "Todos los campos son obligatorios.";
            }
        }

        foreach ([
            'largo' => $b['largo'],
            'ancho' => $b['ancho'],
            'metros_cuadrados' => $b['metros_cuadrados'],
            'valor_m2' => $b['valor_m2'],
            'total' => $b['total']
        ] as $campo => $val) {
            if (!is_numeric($val) || (float) $val <= 0) {
                return "El campo $campo debe ser un número mayor a 0.";
            }
        }

        if (abs(round((float) $b['largo'] * (float) $b['ancho'], 2) - round((float) $b['metros_cuadrados'], 2)) > 0.01) {
            return "Los metros cuadrados no coinciden con largo × ancho.";
        }

        if (!in_array($b['estado'], ['pendiente', 'aprobada', 'rechazada'])) {
            return "Estado inválido.";
        }

        return null;
    }

    
    private function validarRelaciones(array $b): ?string
    {
        if (!$this->model->clienteExiste((int) $b['cliente_id'])) {
            return "El cliente no existe.";
        }

        $inv = $this->model->getInvernadero((int) $b['invernadero_id']);
        if (!$inv) {
            return "El invernadero no existe.";
        }

        if (abs(round((float) $inv['precio_m2'], 2) - round((float) $b['valor_m2'], 2)) > 0.01) {
            return "El valor m² no coincide con el precio del invernadero.";
        }

        if (abs(round((float) $b['metros_cuadrados'] * (float) $b['valor_m2'], 2) - round((float) $b['total'], 2)) > 0.01) {
            return "El total no coincide con metros cuadrados × valor m².";
        }

        return null;
    }

    public function index()
    {
        // ?selects=1 → clientes e invernaderos activos para los <select>
        if (isset($_GET['selects'])) {
            $data = $this->model->getSelects();
            Response::json(['success' => true, 'clientes' => $data['clientes'], 'invernaderos' => $data['invernaderos']]);
            return;
        }

        // ?documento=123 → cotizaciones de un cliente (vista MisCotizaciones)
        if (isset($_GET['documento'])) {
            if (!is_numeric($_GET['documento'])) {
                Response::json(['success' => false, 'mensaje' => 'Documento inválido.'], 400);
                return;
            }
            $rows = $this->model->getByCliente((int) $_GET['documento']);
            Response::json(['success' => true, 'data' => $rows]);
            return;
        }

        // Listado completo (vista admin CotizacionesCRUD)
        $rows = $this->model->getAll();
        Response::json(['success' => true, 'data' => $rows]);
    }

    /**
     * POST /cotizaciones
     */
    public function store()
    {
        $b = json_decode(file_get_contents('php://input'), true) ?? [];

        $error = $this->validarCampos($b);
        if ($error) {
            Response::json(['success' => false, 'message' => $error], 400);
            return;
        }

        $error = $this->validarRelaciones($b);
        if ($error) {
            Response::json(['success' => false, 'message' => $error], 400);
            return;
        }

        if ($this->model->create($b)) {
            Response::json(['success' => true, 'message' => 'Cotización registrada exitosamente.'], 201);
        } else {
            Response::json(['success' => false, 'message' => 'Error al registrar la cotización.'], 500);
        }
    }

    /**
     * PUT /cotizaciones/{id}
     */
    public function update(int $id)
    {
        $cotizacion = $this->model->getById($id);
        if (!$cotizacion) {
            Response::json(['success' => false, 'message' => 'La cotización no existe.'], 404);
            return;
        }

        $b = json_decode(file_get_contents('php://input'), true) ?? [];

        $error = $this->validarCampos($b);
        if ($error) {
            Response::json(['success' => false, 'message' => $error], 400);
            return;
        }

        $error = $this->validarRelaciones($b);
        if ($error) {
            Response::json(['success' => false, 'message' => $error], 400);
            return;
        }

        if ($this->model->update($id, $b)) {
            Response::json(['success' => true, 'message' => 'Cotización actualizada exitosamente.']);
        } else {
            Response::json(['success' => false, 'message' => 'Error al actualizar la cotización.'], 500);
        }
    }

    /**
     * DELETE /cotizaciones/{id}  → rechaza (soft-delete)
     */
    public function destroy(int $id)
    {
        $cotizacion = $this->model->getById($id);
        if (!$cotizacion) {
            Response::json(['success' => false, 'message' => 'La cotización no existe.'], 404);
            return;
        }

        if ($cotizacion['estado'] === 'rechazada') {
            Response::json(['success' => false, 'message' => 'La cotización ya está rechazada.'], 409);
            return;
        }

        if ($this->model->rechazar($id)) {
            Response::json(['success' => true, 'message' => 'Cotización rechazada exitosamente.']);
        } else {
            Response::json(['success' => false, 'message' => 'Error al rechazar la cotización.'], 500);
        }
    }
}