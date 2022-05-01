<?php
namespace App\controller;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Container\ContainerInterface;
use PDO;
class Cliente {
    protected $container;
    public function __construct(ContainerInterface $c) {
        $this->container = $c;
    }
    public function filtrar (Request $request, Response $response, $args) {
        //Retornar todos los registros con limit
        $limite = $args['limite'];
        $indice = ($args['indice'] - 1) * $limite;
        $datos = $request->getQueryParams();
        $cadena = "";
        foreach($datos as $valor){
            $cadena .= "%$valor%&";
        }

        $sql = "call filtrarCliente('$cadena', $indice, $limite);";
        $con = $this->container->get('bd');
        $query = $con->prepare($sql);
        $query->execute();
        $status = $query->rowCount() > 0 ? 200 : 204;
        $res = $query->fetchAll();

        $query = null;
        $con = null;

        $response->getBody()->write(json_encode($res));
        return $response
            ->withHeader('Content-Type', 'Application/json')
            ->withStatus($status);
    }
    public function buscar (Request $request, Response $response, $args) {
        //Retornar un registro por código
       // $id = $args['id'];
        $sql = "call buscarCliente(:id);";
        $con = $this->container->get('bd');
        $query = $con->prepare($sql);
        $query->bindParam('id', $args['id'], PDO::PARAM_STR);
        $query->execute();
        if ($query->rowCount() > 0) {
            $res = $query->fetch();
            $status = 200;
        } else {
            $res = [];
            $status = 204;
        }
        $query = null;
        $con = null;
        $response->getBody()->write(json_encode($res));
        return $response
            ->withHeader('Content-Type', 'Application/json')
            ->withStatus($status);
    }
    public function numRegs(Request $request, Response $response, $args) {
        $datos = $request->getQueryParams();
        $cad = "";
        foreach($datos as $valor) {
            $cad .= "%$valor%&";
        }
        $con = $this->container->get('bd');
        $query = $con->prepare("call numRegsCliente(:cadena);");

        $query->bindParam(':cadena', $cad, PDO::PARAM_STR);
        $query->execute();
        $res['cant'] = $query->fetch(PDO::FETCH_NUM)[0];
        //$res['cant'] = $res[0];
        $query = null;
        $con = null;
        $response->getBody()->write(json_encode($res));
        return $response
            ->withHeader('Content-Type', 'Application/json')
            ->withStatus(200);
    }
    public function crear (Request $request, Response $response, $args) {
        //Crear nuevo
        $body = json_decode($request->getBody());
        $sql = "select nuevoCliente(";
        foreach($body as $campo => $valor) {
            $sql .= ":$campo,";
            $d[$campo] = filter_var($valor, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        }
        $sql = rtrim($sql, ',') . ");";
        $con = $this->container->get('bd');
        $query = $con->prepare($sql);
        $query->execute($d);
        $res = $query->fetch(PDO::FETCH_NUM);
        $query = null;
        $con = null;

        $status = $res[0] > 0 ? 409 : 201;

       // $response->getBody()->write(json_encode($resultado));
        return $response
            ->withStatus($status);
    }
    public function modificar (Request $request, Response $response, $args) {
        //Crear nuevo
        //$id = $args['id'];
        $body = json_decode($request->getBody());
        $sql = "select editarCliente(:id,";
        $d['id'] =  $args['id'];
        foreach($body as $campo => $valor) {
            $sql .= ":$campo,";
            $d[$campo] = filter_var($valor, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        }
        $sql = rtrim($sql, ',') . ");";
        $con = $this->container->get('bd');
        $query = $con->prepare($sql);
        $query->execute($d);
        $res = $query->fetch(PDO::FETCH_NUM);
        $query = null;
        $con = null;

        $status = $res[0] > 0 ? 200 : 404;

       // $response->getBody()->write(json_encode($resultado));
        return $response
            ->withStatus($status);
    }
    public function eliminar (Request $request, Response $response, $args) {
        //$id = $args['id'];
        $sql = "select eliminarCliente(:id);";
        $con = $this->container->get('bd');
        $query = $con->prepare($sql);
        $query->bindParam('id', $args['id'], PDO::PARAM_STR);
        $query->execute();
        $res = $query->fetch(PDO::FETCH_NUM);     
        $query = null;
        $con = null;
        $status = $res[0] > 0 ? 200 : 404;

        //$response->getBody()->write(json_encode($res));
        return $response
            ->withStatus($status);
    }
}