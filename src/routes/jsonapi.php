<?php
    use Psr\Http\Message\ServerRequestInterface as Request;
    use Psr\Http\Message\ResponseInterface as Response;

    require '../vendor/autoload.php';

    function message ($status,$message,$object=null) {
        if ($object == null) {
            return array("status" => $status, "message" => $message);
        } else {
            return array("status" => $status, "message" => $message, "etudiants" => $object);
        }        
    }

    $app = new \Slim\App;
    /**
     * route - CREATE - add new neighbour - POST method
     */
    $app->post
    (
        '/api/voisin', 
        function (Request $request, Response $old_response) {
            try {
                $params = $request->getQueryParams();
                $id = $params['id'];
                $name = $params['name'];
                $prenom = $params['numero'];
                $adresse = $params['adresse'];
                $apropos = $params['apropos'];

                $sql = "insert into voisins (name,numero,adresse,apropos) values (:name,:numero,:adresse,:apropos)";

                $db_access = new DBAccess ();
                $db_connection = $db_access->getConnection();

                $statement = $db_connection->prepare($sql);
                $statement->bindParam(':name', $name);
                $statement->bindParam(':numero', $numero);
                $statement->bindParam(':adresse', $adresse);
                $statement->bindParam(':apropos', $apropos);

                $statement->execute();
                
                $response = $old_response->withHeader('Content-type', 'application/json');
                $body = $response->getBody();
                $body->write(json_encode(message('OK', "The neighbour has been added successfully.")));
            } catch (Exception $exception) {
                
                $response = $old_response->withHeader('Content-type', 'application/json');
                $body = $response->getBody();
                $body->write(json_encode(message('KO', $exception->getMessage())));
            }

            return $response;
        }
    );

    /**
     * route - READ - get neighbour by id - GET method
     */
    $app->get
    (
        '/api/voisin/{id}', 
        function (Request $request, Response $old_response) {
            try {
                $id = $request->getAttribute('id');                

                $sql = "select * from voisin where id = :id";

                $db_access = new DBAccess ();
                $db_connection = $db_access->getConnection();

                $response = $old_response->withHeader('Content-type', 'application/json');
                $body = $response->getBody();

                $statement = $db_connection->prepare($sql);
                $statement->execute(array(':id' => $id));
                if ($statement->rowCount()) {
                    $etudiant = $statement->fetch(PDO::FETCH_OBJ);                    
                    $body->write(json_encode($etudiant));
                }
                else
                {
                    $body->write(json_encode(message('KO', "The neighbour with id = '".$id."' has not been found or has been deleted.")));
                }

                $db_access->releaseConnection();
            } catch (Exception $exception) {
                $response = $old_response->withHeader('Content-type', 'application/json');
                $body = $response->getBody();
                $body->write(json_encode(message('KO', "Unable to connect to the data base.")));
            }
            
            return $response;
        }
    );

    /**
     * route - READ - get all neighbours - GET method
     */
    $app->get
    (
        '/api/voisins', 
        function (Request $request, Response $old_response) {
            try {
                $sql = "Select * From voisin";
                $db_access = new DBAccess ();
                $db_connection = $db_access->getConnection();
    
                $response = $old_response->withHeader('Content-type', 'application/json');
                $body = $response->getBody();

                $statement = $db_connection->query($sql);
                if ($statement->rowCount()) {
                    $etudiants = $statement->fetchAll(PDO::FETCH_OBJ);                    
                    $body->write(json_encode(array("etudiants" => $etudiants)));
                } else {
                    $body->write(json_encode(message('KO', "No neighbour has been recorded yet.")));
                }

                $db_access->releaseConnection();
            } catch (Exception $exception) {
                $response = $old_response->withHeader('Content-type', 'application/json');
                $body = $response->getBody();
                $body->write(json_encode(array("code" => 500, "status" => 'KO', "message" => "Unable to connect to the data base.")));
            }
    
            return $response;
        }
    );

    /**
     * route - UPDATE - update a neighbour by id - PUT method
     */
    $app->put
    (
        '/api/voisin/{id}', 
        function (Request $request, Response $old_response) {
            try {

                $id = $request->getAttribute('id');

                $params = $request->getQueryParams();
                $name = $params['name'];
                $numero = $params['numero'];
                $adresse = $params['adresse'];
                $apropos = $params['apropos'];


                $sql = "update voisin set name = :name, numero = :numero, adresse = :adresse 
                        apropos = :apropos where id = :id";

                $db_access = new DBAccess ();
                $db_connection = $db_access->getConnection();

                $statement = $db_connection->prepare($sql);
                $statement->bindParam(':name', $name);
                $statement->bindParam(':numero', $numero);
                $statement->bindParam(':adresse', $adresse);
                $statement->bindParam(':apropos', $apropos);
                $statement->bindParam(':id', $id);
                $statement->execute();


                $db_access->releaseConnection();

                $response = $old_response->withHeader('Content-Type', 'application/json');
                $body = $response->getBody();
                $body->write(json_encode(message('OK', "The neighbour has been updated successfully.")));
            } catch (Exception $exception) {
                $response = $old_response->withHeader('Content-Type', 'application/json');
                $body = $response->getBody();
                $body->write(json_encode(message('KO', "Unable to connect to the data base.")));
            }

            return $response;
        }
    );

    /**
     * route - DELETE - delete a neighbour by id - DELETE method
     */
    $app->delete
    (
        '/api/etudiant/{id}', 
        function (Request $request, Response $old_response) {
            try {
                $id = $request->getAttribute('id');

                $sql = "delete from voisin where id = :id";

                $db_access = new DBAccess ();
                $db_connection = $db_access->getConnection();

                $response = $old_response->withHeader('Content-type', 'application/json');
                $body = $response->getBody();

                $statement = $db_connection->prepare($sql);
                $statement->execute(array(':id' => $id));

                $body->write(json_encode(message('OK', "The neighbour has been deleted successfully.")));
                $db_access->releaseConnection();
            } catch (Exception $exception) {
                $response = $old_response->withHeader('Content-type', 'application/json');
                $body = $response->getBody();
                $body->write(json_encode(message('KO', "Unable to connect to the data base.")));
            }

            return $response;
        }
    );

     /**
     * route - UPDATE - update a neighour by id - PUT method
     */
    $app->put
    (
        '/api/voisin/remove/{id}', 
        function (Request $request, Response $old_response) {
            try {

                $id = $request->getAttribute('id');

                $params = $request->getQueryParams();
                $favoris = 0;


                $sql = "update voisin set favoris = :favoris where id = :id";

                $db_access = new DBAccess ();
                $db_connection = $db_access->getConnection();

                $statement = $db_connection->prepare($sql);
                $statement->bindParam(':favoris', $favoris);
                $statement->bindParam(':id', $id);
                $statement->execute();


                $db_access->releaseConnection();

                $response = $old_response->withHeader('Content-Type', 'application/json');
                $body = $response->getBody();
                $body->write(json_encode(message('OK', "The neighbour has been successfully mark as favorites .")));
            } catch (Exception $exception) {
                $response = $old_response->withHeader('Content-Type', 'application/json');
                $body = $response->getBody();
                $body->write(json_encode(message('KO', "Unable to connect to the data base.")));
            }

            return $response;
        }
    );

     /**
     * route - UPDATE - update a neighour by id - PUT method
     */
    $app->put
    (
        '/api/voisin/fav/{id}', 
        function (Request $request, Response $old_response) {
            try {

                $id = $request->getAttribute('id');

                $params = $request->getQueryParams();
                $favoris = 1;


                $sql = "update voisin set favoris = :favoris where id = :id";

                $db_access = new DBAccess ();
                $db_connection = $db_access->getConnection();

                $statement = $db_connection->prepare($sql);
                $statement->bindParam(':favoris', $favoris);
                $statement->bindParam(':id', $id);
                $statement->execute();


                $db_access->releaseConnection();

                $response = $old_response->withHeader('Content-Type', 'application/json');
                $body = $response->getBody();
                $body->write(json_encode(message('OK', "The neighbour has been successfully mark as favorites .")));
            } catch (Exception $exception) {
                $response = $old_response->withHeader('Content-Type', 'application/json');
                $body = $response->getBody();
                $body->write(json_encode(message('KO', "Unable to connect to the data base.")));
            }

            return $response;
        }
    );

    $app->run();
?>