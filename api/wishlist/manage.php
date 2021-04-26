<?php
include_once 'WishlistDatabase.php';

$json = file_get_contents('php://input');
$data = json_decode($json, true);

$response = array("status" => 0, "msg" => "failed");

if (isset($data['header']) && isset($data['payload'])) {

    $header = $data['header'];
    $payload = $data['payload'];

    if (isset($payload['operation'])) {
        $wishlist = new WishlistDatabase();

         if ($payload['operation'] == 'GET_ALL') {
            $response = $wishlist->getAll();

        }
          elseif ($payload['operation'] == "ADD") {
            if (isset($payload['userId']) && ($payload['productId'])) {

                $wishlist = new WishlistDatabase();
                $response = $wishlist->add($payload['userId'], $payload['productId']);

            } else {
                $response = array("status" => 0, "msg" => "Invalid input");
            }
        }
    } else {

        $response = array("status" => 0, "msg" => "Please specfify operation");
    }
}

echo json_encode($response);
