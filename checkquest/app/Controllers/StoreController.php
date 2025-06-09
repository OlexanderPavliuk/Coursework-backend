<?php
namespace App\Controllers;

use Core\Controller;
use Core\Csrf;
use App\Models\Store;

class StoreController extends Controller
{
    public function purchaseItem(): void
    {
        $this->requireAuth();
        $data = json_decode(file_get_contents('php://input'), true);

        if (!Csrf::verify($data['csrf_token'] ?? '')) {
            http_response_code(419);
            echo json_encode(['success' => false, 'error' => 'CSRF mismatch']);
            return;
        }

        try {
            $model     = new Store();
            $response  = $model->handlePurchase(
                $_SESSION['user_id'],
                (int) $data['itemId']
            );

            echo json_encode(['success' => true] + $response);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
