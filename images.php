<?php

require_once 'conexao.php';

header("Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS, DELETE");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");


$image = $_FILES['file'];
$dir = $_POST['folder'];
$imagem = upload_images($image, $dir);
if (!empty($imagem)) {
    $result = save_images_db((object) $imagem);
   if (!empty($result)) echo json_encode($result); exit;
}

echo json_encode(array('status' => false, 'message' => 'Falha no carregamento da imagem, tente com outra imagem'));
exit;


function upload_images($image, $dir)
{
    if (!in_array($image['type'], array('image/png', 'image/gif', 'image/jpeg', 'image/jpg'))) {
        echo json_encode(array('status' => false, 'message' => 'Formato de imagem inválido, por favor selecione imagens nos formatos: jpg, png ou gif.'));
        exit;
    }

    $ext = explode('/', $image['type'])[1];
    $tmpname = md5(time().rand(0,9999)) . '.' . $ext;
    $target_dir = "uploads/";
    // $target_dir = "/uploads/gold_luck/" . $dir;
    $target_file = $target_dir . basename($tmpname);

    // Limit 5MB size image
    if ($image['size'] > 5000000) {
        echo json_encode(array('status' => false, 'message' => 'Tamanho do arquivo excedeu o limite de 5mb'));
        exit;
    }

    // Create dir not exists
    if (!is_dir($target_dir))
        mkdir($target_dir , 0755, true);

    $response = array();
    if (move_uploaded_file($image['tmp_name'], $target_dir . $tmpname)) {
        $response = array(
            'url' => '/uploads/'.explode('uploads/', $target_file)[1],
            'name' => $tmpname,
            'original_name' => $image['name'],
            'size' => $image['size'],
            'type' => $image['type']
        );
    }
    return $response;
}

function save_images_db($data)
{
    global $db;
    $sql = "INSERT INTO images (name, url, original_name, type, size) VALUES (?,?,?,?,?)";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(1, $data->name, PDO::PARAM_STR);
    $stmt->bindParam(2, $data->url, PDO::PARAM_STR);
    $stmt->bindParam(3, $data->original_name, PDO::PARAM_STR);
    $stmt->bindParam(4, $data->type, PDO::PARAM_STR);
    $stmt->bindParam(5, $data->size, PDO::PARAM_STR);
    $stmt->execute();

    $result = array();
    if ($stmt->rowCount() > 0) {
        $sql = "SELECT * FROM images WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':id',  $db->lastInsertId(), PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch();
    }
    return $result;
}