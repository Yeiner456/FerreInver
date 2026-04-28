<?php

require_once __DIR__ . '/../models/PedidosModel.php';
require_once __DIR__ . '/../core/Response.php';

class PedidosController {

    private PedidosModel $model;

    private array $mediosValidos  = ['Efectivo', 'Tarjeta Débito', 'Tarjeta Crédito', 'Transferencia', 'PSE', 'Nequi', 'Daviplata'];
    private array $estadosValidos = ['pendiente', 'recibido', 'listo para recibir', 'cancelado'];

    public function __construct() {
        $this->model = new PedidosModel();
    }

    // ─── GET /pedidos               → lista completa ─────────────────────────
    // ─── GET /pedidos?selects=1     → clientes para el <select> ──────────────
    // ─── GET /pedidos?documento=X   → pedidos de un cliente (vista cliente) ──
    public function index(): void {
        // Vista cliente: pedidos por documento
        if (isset($_GET['documento'])) {
            $documento = $_GET['documento'];
            if (!is_numeric($documento) || $documento <= 0) {
                Response::error("Documento inválido.", 400); return;
            }
            $data = $this->model->getByCliente((int)$documento);
            Response::success("OK", $data);
            return;
        }

        // Select de clientes para el modal
        if (isset($_GET['selects'])) {
            $clientes = $this->model->getClientesSelect();
            Response::success("OK", ['clientes' => $clientes]);
            return;
        }

        // Lista completa (admin)
        $data = $this->model->getAll();
        Response::success("OK", $data);
    }

    // ─── POST /pedidos  → crear pedido simple (admin) ─────────────────────────
    public function create(): void {
        $b = json_decode(file_get_contents("php://input"), true);

        $id_cliente    = $b['id_cliente']    ?? '';
        $medio_pago    = trim($b['medio_pago']    ?? '');
        $estado_pedido = trim($b['estado_pedido'] ?? '');

        if (empty($id_cliente) || empty($medio_pago) || empty($estado_pedido)) {
            Response::error("Todos los campos son obligatorios.", 400); return;
        }
        if (!is_numeric($id_cliente) || $id_cliente <= 0) {
            Response::error("ID de cliente inválido.", 400); return;
        }
        if (!in_array($medio_pago, $this->mediosValidos)) {
            Response::error("Medio de pago inválido.", 400); return;
        }
        if (!in_array($estado_pedido, $this->estadosValidos)) {
            Response::error("Estado del pedido inválido.", 400); return;
        }
        if (!$this->model->clienteExiste((int)$id_cliente)) {
            Response::error("El cliente no existe.", 404); return;
        }

        if ($this->model->create((int)$id_cliente, $medio_pago, $estado_pedido))
            Response::success("Pedido registrado exitosamente.", null, 201);
        else
            Response::error("Error al registrar el pedido.", 500);
    }

    // ─── POST /pedidos/completo  → crear pedido con items (carrito cliente) ───
    public function createCompleto(): void {
        $b = json_decode(file_get_contents("php://input"), true);

        $id_cliente = $b['id_cliente'] ?? '';
        $medio_pago = trim($b['medio_pago'] ?? '');
        $items      = $b['items'] ?? [];

        if (empty($id_cliente) || empty($medio_pago) || empty($items)) {
            Response::error("Faltan datos obligatorios.", 400); return;
        }
        if (!in_array($medio_pago, $this->mediosValidos)) {
            Response::error("Medio de pago inválido.", 400); return;
        }
        if (!is_array($items) || count($items) === 0) {
            Response::error("El carrito está vacío.", 400); return;
        }
        if (!$this->model->clienteExiste((int)$id_cliente)) {
            Response::error("El cliente no existe.", 404); return;
        }

        $id_pedido = $this->model->createCompleto((int)$id_cliente, $medio_pago, $items);

        if ($id_pedido !== false)
            Response::success("Pedido registrado exitosamente.", ['id_pedido' => $id_pedido], 201);
        else
            Response::error("Error al registrar el pedido.", 500);
    }

    // ─── PUT /pedidos?id=X ────────────────────────────────────────────────────
    public function update(?string $id): void {
        if (!$id || !is_numeric($id)) {
            Response::error("ID inválido.", 400); return;
        }

        $id = (int)$id;
        $b  = json_decode(file_get_contents("php://input"), true);

        $id_cliente    = $b['id_cliente']    ?? '';
        $medio_pago    = trim($b['medio_pago']    ?? '');
        $estado_pedido = trim($b['estado_pedido'] ?? '');

        if (empty($id_cliente) || empty($medio_pago) || empty($estado_pedido)) {
            Response::error("Todos los campos son obligatorios.", 400); return;
        }
        if (!is_numeric($id_cliente) || $id_cliente <= 0) {
            Response::error("ID de cliente inválido.", 400); return;
        }
        if (!in_array($medio_pago, $this->mediosValidos)) {
            Response::error("Medio de pago inválido.", 400); return;
        }
        if (!in_array($estado_pedido, $this->estadosValidos)) {
            Response::error("Estado del pedido inválido.", 400); return;
        }
        if (!$this->model->getById($id)) {
            Response::error("El pedido no existe.", 404); return;
        }
        if (!$this->model->clienteExiste((int)$id_cliente)) {
            Response::error("El cliente no existe.", 404); return;
        }

        if ($this->model->update($id, (int)$id_cliente, $medio_pago, $estado_pedido))
            Response::success("Pedido actualizado exitosamente.");
        else
            Response::error("Error al actualizar el pedido.", 500);
    }

    // ─── DELETE /pedidos?id=X  (soft → estado = 'cancelado') ─────────────────
    public function cancel(?string $id): void {
        if (!$id || !is_numeric($id)) {
            Response::error("ID inválido.", 400); return;
        }

        $id     = (int)$id;
        $pedido = $this->model->getById($id);

        if (!$pedido) {
            Response::error("El pedido no existe.", 404); return;
        }
        if ($pedido['estado_pedido'] === 'cancelado') {
            Response::error("El pedido ya está cancelado.", 409); return;
        }

        if ($this->model->cancel($id))
            Response::success("Pedido cancelado exitosamente.");
        else
            Response::error("Error al cancelar el pedido.", 500);
    }
}