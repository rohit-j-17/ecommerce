<?php
include_once 'ProductDatabase.php';
include_once '../../enum/EResponseCode.php';

$json = file_get_contents('php://input');
$data = json_decode($json, true);

$response = array("status" => EResponseCode::FAILED, "msg" => "failed");

if (isset($data['header']) && isset($data['payload'])) {

    $header = $data['header'];
    $payload = $data['payload'];

    if (isset($payload['operation'])) {
        $product = new ProductDatabase();

        if ($payload['operation'] == 'GET') {

            if (isset($payload['productId'])) {
                $response = $product->get($payload['productId']);
            } else {
                $response = array("status" => 0, "msg" => "invalid input");
            }
        } else if ($payload['operation'] == 'GET_ALL') {

            $response = $product->getAll();
        } else if ($payload['operation'] == 'GET_ALL_BY_CATEGORY') {

            if (isset($payload['categoryId'])) {
                $response = $product->getAllByCategory($payload['categoryId']);
            } else {
                $response = array("status" => 0, "msg" => "invalid input");
            }
        } else if ($payload['operation'] == 'GET_ALL_BY_SUBCATEGORY') {

            if (isset($payload['subCategoryId'])) {
                $response = $product->getAllBySubcategory($payload['subCategoryId']);
            } else {
                $response = array("status" => 0, "msg" => "invalid input");
            }
        } else if ($payload['operation'] == 'DELETE') {

            if (isset($payload['productId'])) {
                $response = $product->delete($payload['productId']);
            } else {
                $response = array("status" => 0, "msg" => "invalid input");
            }
        } else if ($payload['operation'] == 'UPDATE') {

            if (isset($payload['product']) && isset($payload['productImages'])) {
                $productobj = $payload['product'];
                if (isset($productobj['productId']) && isset($productobj['categoryId']) &&
                    isset($productobj['subCategoryId']) && isset($productobj['sCode']) &&
                    isset($productobj['sName']) && isset($productobj['sDescription']) &&
                    isset($productobj['sPrice']) && isset($productobj['sSize'])) {
                    $response = $product->update($productobj['productId'], $productobj['categoryId'],
                        $productobj['subCategoryId'], $productobj['sCode'], $productobj['sName'],
                        $productobj['sDescription'], $productobj['sPrice'], $productobj['sSize'],
                        $payload['productImages']);
                } else {
                    return array("status" => EResponseCode::INVALID_INPUT, "msg" => "invalid input");
                }
            } else {
                eturn array("status" => EResponseCode::INVALID_INPUT, "msg" => "invalid input");
            }
        }
    } else {
        return array("status" => EResponseCode::INVALID_INPUT, "msg" => "Please specify operation");
    }

}

echo json_encode($response);
