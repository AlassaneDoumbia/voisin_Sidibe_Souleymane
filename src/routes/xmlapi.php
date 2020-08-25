<?php
    use Psr\Http\Message\ServerRequestInterface as Request;
    use Psr\Http\Message\ResponseInterface as Response;

    require '../vendor/autoload.php';

    function xml_encode ($array, $xmlRootElement=null) {
        $string_xml = "";
        if ( $xmlRootElement != null ) $string_xml = "<$xmlRootElement>";
        foreach ($array as $element => $content) {
            $string_xml = $string_xml."<$element>";
            $string_xml = $string_xml.$content;
            $string_xml = $string_xml."</$element>";
        }
        if ( $xmlRootElement !=null ) $string_xml = $string_xml."</$xmlRootElement>";
        return $string_xml;
    }

    function xml_encode_array ($array, $xmlElement) {
        $string_xml = "";
        foreach ($array as $element) {
            $string_xml = $string_xml.xml_encode($element, $xmlElement);
        }
        return $string_xml;
    }

    function message ($status,$message,$object=null) {
        if ($object == null) {
            return array("status" => $status, "message" => $message);
        } else {
            return array("status" => $status, "message" => $message, "etudiants" => $object);
        }        
    }

    $app = new \Slim\App;
    /**
     * route - CREATE - add new student - POST method
     */
    $app->post
    (
        '/api/etudiant', 
        function (Request $request, Response $old_response, array $args) {
            try {
                $params = $request->getQueryParams();
                $id = $params['id'];
                $nom = $params['nom'];
                $prenom = $params['prenom'];
                $moyenne = $params['moyenne'];

                $sql = "insert into T_Etudiants (id,nom,prenom,moyenne) values (:id,:nom,:prenom,:moyenne)";

                $db_access = new DBAccess ();
                $db_connection = $db_access->getConnection();

                $statement = $db_connection->prepare($sql);
                $statement->bindParam(':id', $id);
                $statement->bindParam(':nom', $nom);
                $statement->bindParam(':prenom', $prenom);
                $statement->bindParam(':moyenne', $moyenne);
                $statement->execute();
                
                $db_access->releaseConnection();
                $response = $old_response->withHeader('Content-type', 'application/xml');
                $body = $response->getBody();
                $body->write(xml_encode(message('OK', "The student has been added successfully."), "response"));
            } catch (Exception $exception) {         
                $response = $old_response->withHeader('Content-type', 'application/xml');
                $body = $response->getBody();
                $body->write(xml_encode(message('KO', $exception->getMessage()), "response"));            
            }

            return $response;
        }
    );

    /**
     * route - READ - get student by id - GET method
     */
    $app->get
    (
        '/api/etudiant/{id}', 
        function (Request $request, Response $old_response, array $args) {
            try {
                $id = $request->getAttribute('id');                

                $sql = "select * from T_Etudiants where id = :id";

                $db_access = new DBAccess ();
                $db_connection = $db_access->getConnection();
                
                $response = $old_response->withHeader('Content-type', 'application/xml');
                $body = $response->getBody();

                $statement = $db_connection->prepare($sql);
                $statement->execute(array(':id' => $id));
                if ($statement->rowCount()) {
                    $etudiant = $statement->fetch(PDO::FETCH_OBJ);
                    $etudiant_array = array( 
                        "id" => $etudiant->id, 
                        "nom" => $etudiant->nom,
                        "prenom" => $etudiant->prenom,
                        "moyenne" => $etudiant->moyenne);              
                    $body->write(xml_encode($etudiant_array, "etudiant"));
                }
                else
                {                    
                    $body->write(xml_encode(message('KO', "The student with id = '".$id."' has not been found or has been deleted."), "response"));
                }

                $db_access->releaseConnection();
            } catch (Exception $exception) {                
                $response = $old_response->withHeader('Content-type', 'application/xml');
                $body = $response->getBody();
                $body->write(xml_encode(message('KO', "Unable to connect to the data base."), "response"));
            }
            
            return $response;
        }
    );

    /**
     * route - READ - get all students - GET method
     */
    $app->get
    (
        '/api/etudiants', 
        function (Request $request, Response $old_response) {
            try {
                $sql = "Select * From T_Etudiants";
                $db_access = new DBAccess ();
                $db_connection = $db_access->getConnection();
    
                $response = $old_response->withHeader('Content-type', 'application/xhtml+xml');
                $body = $response->getBody();

                $statement = $db_connection->query($sql);
                if ($statement->rowCount()) {
                    $etudiants = $statement->fetchAll(PDO::FETCH_OBJ);
                    $etudiant_str = xml_encode_array ($etudiants, "etudiant");
                    $body->write(xml_encode(array("etudiants" => $etudiant_str)));
                } else {
                    $body->write(json_encode(message('KO', "No student has been recorded yet.")));
                }

                $db_access->releaseConnection();
            } catch (Exception $exception) {
                $response = $old_response->withHeader('Content-type', 'application/xml');
                $body = $response->getBody();
                $body->write(xml_encode(message('KO', "Unable to connect to the data base."), "response"));
            }
    
            return $response;
        }
    );

    /**
     * route - UPDATE - update a student by id - PUT method
     */
    $app->put
    (
        '/api/etudiant/{id}', 
        function (Request $request, Response $old_response) {
            try {

                $id = $request->getAttribute('id');

                $params = $request->getQueryParams();
                $nom = $params['nom'];
                $prenom = $params['prenom'];
                $moyenne = $params['moyenne'];

                $sql = "update T_Etudiants set nom = :nom, prenom = :prenom, moyenne = :moyenne where id = :id";

                $db_access = new DBAccess ();
                $db_connection = $db_access->getConnection();

                $statement = $db_connection->prepare($sql);
                $statement->bindParam(':nom', $nom);
                $statement->bindParam(':prenom', $prenom);
                $statement->bindParam(':moyenne', $moyenne);
                $statement->bindParam(':id', $id);
                $statement->execute();

                $db_access->releaseConnection();

                $response = $old_response->withHeader('Content-type', 'application/xhtml+xml');
                $body = $response->getBody();
                $body->write(xml_encode(message('OK', "The student has been updated successfully."), "response"));
            } catch (Exception $exception) {
                $response = $old_response->withHeader('Content-Type', 'application/xml');
                $body = $response->getBody();
                $body->write(xml_encode(message('KO', "Unable to connect to the data base.")));
            }

            return $response;
        }
    );

    /**
     * route - DELETE - delete a student by id - DELETE method
     */
    $app->delete
    (
        '/api/etudiant/{id}', 
        function (Request $request, Response $old_response, array $args) {
            try {
                $id = $request->getAttribute('id');

                $sql = "delete from T_Etudiants where id = :id";

                $db_access = new DBAccess ();
                $db_connection = $db_access->getConnection();

                $response = $old_response->withHeader('Content-type', 'application/xml');
                $body = $response->getBody();

                $statement = $db_connection->prepare($sql);
                $statement->execute(array(':id' => $id));

                $body->write(xml_encode(message('OK', "The student has been deleted successfully."), "response"));
                $db_access->releaseConnection();
            } catch (Exception $exception) {
                $response = $old_response->withHeader('Content-type', 'application/xml');
                $body = $response->getBody();
                $body->write(xml_encode(message('KO', "Unable to connect to the data base.")));
            }
            return $response;
        }
    );

    $app->run();
?>