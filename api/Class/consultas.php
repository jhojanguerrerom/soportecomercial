<?php
//error_reporting(0);
require_once 'db.php';

error_reporting(E_ALL);
ini_set('display_errors', '1');

class consultas
{
    private $_DB;

    public function __construct()
    {
        $this->_DB = new DB;
    }

    public function request_connector($url, $query) {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>'{
                "query": "'.$query.'"
            }',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
		'Authorization: ERFYCOSpbE0gdTvgVVINlvDrljICFEU5INxss9Rxwuf57THfozjmFUjqD337Dgc9A2HWdwFsLGF6GYMHxfUmSqetfR4GSK6xW2fEsv1qdNlHQ9DcY6Qo7p8yrdn7iKdM',
            ),
        ));

        $response = curl_exec($curl);
        $data = json_decode($response, true);
        curl_close($curl);

        return $data;
    }

    public function mapper_estado($estado) {
        try {
            $mapper_estado = [
                "Abierto" => "Abierto",
                "Aceptado" => "Aceptado",
                "Asignado" => "Asignado",
                "Cancelado en ruta" => "Cancelado",
                "Cancelado" => "Cancelado",
                "Despachado" => "Despachado",
                "Dispatched" => "Despachado",
                "En Camino" => "En Camino",
                "En camino" => "En Camino",
                "En Sitio" => "En Sitio",
                "En sitio" => "En Sitio",
                "Fallida con visita" => "Fallida Con Visita",
                "Fallido sin visita" => "Fallida Sin Visita",
                "Finalizada" => "Finalizada",
                "Finalizado" => "Finalizada",
                "Incompleto" => "Incompleto",
                "Pendiente" => "Pendiente",
                "Rechazado" => "Rechazado",
                "Suspendida" => "Suspendido",
                "Suspendido-Abierto" => "Suspendido",
                "Suspendido" => "Suspendido",
            ];
            if (array_key_exists($estado, $mapper_estado)) {
                return $mapper_estado[$estado];
            } else {
                return $estado; // Si la clave no existe, devuelve el estado original
            }
        } catch (\Throwable $th) {
            return $estado;
        }
    }

    public function visitasTerreno($data)
    {
        try {
            if ($data['cedula'] === 'vacio') {
                require_once 'Contador.php';
                $contador = new Contador();
                $contador->Contador('Gescom');
            } else {
                require_once 'Contador.php';
                $contador = new Contador();
                $contador->Contador('VisitasTerreno');
            }
    
            $pedido = $data['pedido'];
            $cedula = $data['cedula'];
    
            // CLICK
            /* $query_click = "SELECT TOP 1
                c.UNEIdCliente AS CedulaCliente,
                c.UNENombreCliente AS NombreCliente,
                c.UNEDireccion AS DireccionCliente,
                c.Status,
                wts.Name,
                c.UNEPedido AS Pedido,
                c.UNEFechaCita AS FechaCita,
                c.UNEHoraCita AS HoraCita,
                c.UNEUENcalculada AS UEN,
                c.UNESegmento AS Segmento,
                c.UNEProductos AS Productos,
                c.UNETecnologias AS Tecnologias,
                c.EngineerID AS CedulaTecnico,
                c.AssignedEngineerName AS NombreTecnico,
                p.Name AS MicroZona,
                c.CallID AS Tarea,
                c.UNEDoNotDispatch AS EstadoPedido,
                c.TimeModified,
                c.UNEObservation
            FROM
                W6TASKS c
                INNER JOIN W6DISTRICTS AS p ON c.District= p.W6Key
                INNER JOIN W6TASK_STATUSES wts ON wts.W6Key = c.Status
            WHERE
                c.UNEPedido = '$pedido'
                OR c.UNEIdCliente = '$cedula'
            ORDER BY
                c.CallID DESC;"; */

            $query_click = "SELECT TOP 1
                c.UNEIdCliente AS CedulaCliente,
                c.UNENombreCliente AS NombreCliente,
                c.UNEDireccion AS DireccionCliente,
                c.Status,
                wts.Name,
                c.UNEPedido AS Pedido,
                c.UNEFechaCita AS FechaCita,
                c.UNEHoraCita AS HoraCita,
                c.UNEUENcalculada AS UEN,
                c.UNESegmento AS Segmento,
                c.UNEProductos AS Productos,
                c.UNETecnologias AS Tecnologias,
                c.EngineerID AS CedulaTecnico,
                c.AssignedEngineerName AS NombreTecnico,
                p.Name AS MicroZona,
                c.CallID AS Tarea,
                c.UNEDoNotDispatch AS EstadoPedido,
                c.TimeModified,
                c.UNEObservation,
                TCI.Code AS 'Concepto', 
                TCI.Name AS 'IncompleteCode', 
                SERV.ObsTecnico
            FROM
                W6TASKS c
                INNER JOIN W6DISTRICTS AS p ON c.District= p.W6Key
                INNER JOIN W6TASK_STATUSES wts ON wts.W6Key = c.Status
                INNER JOIN W6DISTRICTS DIS ON c.District=DIS.W6Key
                INNER JOIN W6TASKTYPECATEGORY TTC ON c.TaskTypeCategory=TTC.W6Key
                INNER JOIN W6TASK_TYPES TT ON c.TaskType=TT.W6Key
                INNER JOIN W6TASKS_UNESERVICES TK_SERV ON c.W6Key=TK_SERV.W6Key
                INNER JOIN W6UNESERVICES SERV ON TK_SERV.UNEService=SERV.W6Key
                LEFT JOIN W6UNEPENDINGCODES TCP ON SERV.PendingCode=TCP.W6Key
                LEFT JOIN W6UNEINCOMPLETECODES TCI ON SERV.IncompleteCode=TCI.W6Key
            WHERE
                c.UNEPedido = '$pedido'
                OR c.UNEIdCliente = '$cedula'
            ORDER BY
                c.CallID DESC;";
            
            $query_click = preg_replace('/\s+/', ' ', trim($query_click));
    
            $dataclick = $this->request_connector("http://localhost:3001/get", $query_click);
    
//            $rowsAffected_click = $dataclick["rowsAffected"];
//            $recordset_click = $dataclick["recordset"];

	    $rowsAffected_click = 0;
	    $recordset_click = [];

            if ($dataclick !== null && isset($dataclick["rowsAffected"])) {
    	    	$rowsAffected_click = $dataclick["rowsAffected"];
 	    }

	    if ($dataclick !== null && isset($dataclick["recordset"])) {
    		$recordset_click = $dataclick["recordset"];
	    }
            // SALESFORCE
            $conditional = "external_id = '$pedido' AND codigo_de_cliente = '$cedula'";
            if ($cedula == 'vacio') {
                $conditional = "external_id LIKE '%$pedido%'";
            }

            if ($pedido == 'vacio') {
                $conditional = "codigo_de_cliente LIKE '%$cedula%'";
            }

            $sql = "SELECT
                codigo_de_cliente AS \"CedulaCliente\",
                nombre_de_la_cuenta AS \"NombreCliente\",
                direccion AS \"DireccionCliente\",
                estado AS \"Name\",
                external_id AS \"Pedido\",
                inicio_de_cita::date AS \"FechaCita\",
                inicio_de_cita::time AS \"HoraCita\",
                fin_de_cita::date AS \"FechaCitaFin\",
                fin_de_cita::time AS \"HoraCitaFin\",
                '' AS \"UEN\",
                CASE
                    WHEN tipo_de_trabajo LIKE '%Reparacion%' THEN 'Aseguramiento'
                    WHEN tipo_de_trabajo LIKE '%Instalacion%' THEN 'Aprovisionamiento'
                    ELSE tipo_de_trabajo
                END AS \"Segmento\",
                '' AS \"Productos\",
                CASE
                    WHEN tipo_de_trabajo LIKE '%HFC%' THEN 'HFC'
                    WHEN tipo_de_trabajo LIKE '%GPON%' THEN 'GPON'
                    WHEN tipo_de_trabajo LIKE '%FTTH%' THEN 'GPON'
                    WHEN tipo_de_trabajo LIKE '%FO%' THEN 'GPON'
                    ELSE tipo_de_trabajo
                END AS \"Tecnologias\",
                tecnico_identificacion AS \"CedulaTecnico\",
                tecnico_nombre AS \"NombreTecnico\",
                territorio_de_servicio_nombre AS \"MicroZona\",
                numero_de_cita AS \"Tarea\",
                /*CASE
                    WHEN datos_incompletos = '0' THEN '-1'
                    ELSE datos_incompletos
                END AS \"EstadoPedido\"*/
                CASE
                    WHEN comentario_de_crm LIKE '%POE%' AND (dato_de_conexion is null or dato_de_conexion = '') THEN '-1'
                    ELSE '1'
                END AS \"EstadoPedido\",
                motivo_de_fallida AS \"Concepto\",
                motivo_de_fallida AS \"IncompleteCode\",
                fecha_de_la_ultima_modificacion AS \"TimeModified\",
                nota_de_servicio AS \"UNEObservation\",
                descripcion_del_trabajo AS \"ObsTecnico\"
                FROM informe_ayudas_terreno_materialized
                WHERE $conditional AND inicio_de_cita IS NOT NULL
                ORDER BY inicio_de_cita DESC
                LIMIT 1;";

            $stmt = $this->_DB->salesforcedb()->prepare($sql);
            $stmt->execute();

            $rowsAffected_sales_force = $stmt->rowCount();
            $recordset_sales_force = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // $query_salesforce = "SELECT
            //     TOP 1
            //     c.CLIENTE_ID AS CedulaCliente,
            //     c.NOMBRE_CLIENTE AS NombreCliente,
            //     c.DIRECCION AS DireccionCliente,
            //     c.ESTADO_CLICK AS Name,
            //     c.PEDIDO_UNE AS Pedido,
            //     c.FECHACITA AS FechaCita,
            //     c.HORACITA AS HoraCita,
            //     '' AS UEN,
            //     c.SEGMENTO_HOMOLOGADO AS Segmento,
            //     c.PRODUCTO_HOMOLOGADO AS Productos,
            //     c.TECNOLOGIA_HOMOLOGADA AS Tecnologias,
            //     c.COD_FUNCIONARIO AS CedulaTecnico,
            //     c.NOMBRE_FUNCIONARIO AS NombreTecnico,
            //     c.MICROZONA AS MicroZona,
            //     c.TAREA_ID AS Tarea,
            //     c.ESTADO_CLICK AS EstadoPedido,
            //     c.FECHA_MODIFICADO AS TimeModified,
            //     c.OBSERVACION_TAREA AS UNEObservation
            // FROM
            //     View_Pedidos_Terreno_Actual c
            // WHERE c.PEDIDO_UNE = '$pedido'
            //     OR c.CLIENTE_ID = '$cedula'
            // ORDER BY
            //     c.TAREA_ID DESC;";
            
            // $query_salesforce = preg_replace('/\s+/', ' ', trim($query_salesforce));
    
            // $datasalesforce = $this->request_connector("http://localhost:3002/get", $query_salesforce);
    
            // $rowsAffected_sales_force = 0;
            // if ($datasalesforce['type'] == 'success'){
            //     $rowsAffected_sales_force = $datasalesforce["rowsAffected"];
            //     $recordset_sales_force = $datasalesforce["recordset"];
            // }
    
            $fechacitaclick = 0;
            $fechacitasalesforce = 0;
            if ($rowsAffected_click > 0) {
                $fechacitaclick = strtotime($recordset_click[0]['FechaCita']);
            }
    
            if ($rowsAffected_sales_force > 0) {
                $fechacitasalesforce = strtotime($recordset_sales_force[0]['FechaCita']);
            }
    
            if ($fechacitaclick == 0 && $fechacitasalesforce == 0) {
                echo json_encode($recordset_click);
            } else if ($fechacitaclick > 0 &&  $fechacitasalesforce == 0) {
                /* $recordset_click[0]['Name'] = $this->mapper_estado($recordset_click[0]['Name']);
                $recordset_click[0]['EstadoPedido'] = $this->mapper_estado($recordset_click[0]['EstadoPedido']);
                $recordset_click[0]['HoraCita'] = ($recordset_click[0]['HoraCita'] == null) ? 'TD' : $recordset_click[0]['HoraCita']; */
                $dataClick = $this->validacionClick($recordset_click);
                echo json_encode($dataClick);
            } else if ($fechacitaclick == 0 &&  $fechacitasalesforce > 0) {
                /* $recordset_sales_force[0]['Name'] = $this->mapper_estado($recordset_sales_force[0]['Name']);
                $recordset_sales_force[0]['EstadoPedido'] = $this->mapper_estado($recordset_sales_force[0]['EstadoPedido']);
                $recordset_sales_force[0]['HoraCita'] = ($recordset_sales_force[0]['HoraCita'] == null) ? 'TD' : $recordset_sales_force[0]['HoraCita'];
                $recordset_sales_force[0]['CedulaCliente'] = preg_replace('/COB2C/', '', $recordset_sales_force[0]['CedulaCliente']); */
                $dataSalesForce = $this->validacionSalesForce($recordset_sales_force);
                echo json_encode($dataSalesForce);
            } else if ($fechacitaclick > 0 &&  $fechacitasalesforce > 0) {
                if ($fechacitaclick > $fechacitasalesforce) {
                    /* $recordset_click[0]['Name'] = $this->mapper_estado($recordset_click[0]['Name']);
                    $recordset_click[0]['EstadoPedido'] = $this->mapper_estado($recordset_click[0]['EstadoPedido']);
                    $recordset_click[0]['HoraCita'] = ($recordset_click[0]['HoraCita'] == null) ? 'TD' : $recordset_click[0]['HoraCita']; */
                    $dataClick = $this->validacionClick($recordset_click);
                    echo json_encode($dataClick);
                } else {
                    /* $recordset_sales_force[0]['Name'] = $this->mapper_estado($recordset_sales_force[0]['Name']);
                    $recordset_sales_force[0]['EstadoPedido'] = $this->mapper_estado($recordset_sales_force[0]['EstadoPedido']);
                    $recordset_sales_force[0]['HoraCita'] = ($recordset_sales_force[0]['HoraCita'] == null) ? 'TD' : $recordset_sales_force[0]['HoraCita'];
                    $recordset_sales_force[0]['CedulaCliente'] = preg_replace('/COB2C/', '', $recordset_sales_force[0]['CedulaCliente']); */
                    $dataSalesForce = $this->validacionSalesForce($recordset_sales_force);
                    echo json_encode($dataSalesForce);
                }
                
            }
        } catch (\Throwable $th) {
            print_r($th);
        }

    }


    public function validacionClick($recordset_click) {
        try {

            $recordset_click[0]['Name'] = $this->mapper_estado($recordset_click[0]['Name']); // ESTADO DE ORDEN
            $recordset_click[0]['EstadoPedido'] = ($recordset_click[0]['EstadoPedido'] === '-1') ? '-1' : $this->mapper_estado($recordset_click[0]['EstadoPedido']); //Amarilla
            $recordset_click[0]['HoraCita'] = ($recordset_click[0]['HoraCita'] == null) ? 'TD' : $recordset_click[0]['HoraCita'];
            $recordset_click[0]['hasDefMicroZona'] = preg_match('/DEF_/', $recordset_click[0]['MicroZona']);
            $recordset_click[0]['ObsTecnico'] = ($recordset_click[0]['ObsTecnico'] === null || $recordset_click[0]['ObsTecnico'] === '') ? 'Para este pedido el técnico no registro observaciones' : $recordset_click[0]['ObsTecnico'];

            $fecha = date('Y-m-d');

            /* if ($recordset_click[0]['FechaCita'] > $fecha && ($recordset_click[0]['Name'] == 'Abierto' || $recordset_click[0]['Name'] == 'Asignado')) {
                $recordset_click[0]['Name'] = 'Agenda Futura';
            } else if ($recordset_click[0]['FechaCita'] < $fecha) {
                $recordset_click[0]['Name'] = 'Agenda Vencida';
            } */

            $recordset_click[0]['Guion'] = '';
            $pedido = $recordset_click[0]['Pedido'];
            $fechaCita = $recordset_click[0]['FechaCita'];
            $horaCita = $recordset_click[0]['HoraCita'];
            $nombreTecnico = $recordset_click[0]['NombreTecnico'];
            $cedulaTecnico = $recordset_click[0]['CedulaTecnico'];
            $concepto = $recordset_click[0]['Concepto'];

            $segmento = $recordset_click[0]['Segmento'];

            if (preg_match('/Aseguramiento/', $segmento)) {
                if (strtoupper($recordset_click[0]['Name']) === 'ASIGNADO') {
                    $recordset_click[0]['Guion'] = "Tu orden con pedido $pedido tiene fecha de mantenimiento $fechaCita en horario $horaCita, ya cuenta con un técnico, asignado, recuerda contarle al usuario que debe permanecer un mayor de edad durante la visita.";
                } else if (strtoupper($recordset_click[0]['Name']) === 'DESPACHADO') {
                    $recordset_click[0]['Guion'] = "Tu orden con pedido $pedido tiene fecha de mantenimiento $fechaCita en horario $horaCita, ya cuenta con un técnico, asignado, recuerda contarle al usuario que debe permanecer un mayor de edad durante la visita.";
                } else if (strtoupper($recordset_click[0]['Name']) === 'SUSPENDIDA') {
                    $recordset_click[0]['Guion'] = "El pedido $pedido se ha asociado a una falla masiva, sugerimos que contactes al cliente para infórmale la situación";
                } else if (strtoupper($recordset_click[0]['Name']) === 'ABIERTO') {
                    $recordset_click[0]['Guion'] = "Tu orden con pedido $pedido tiene fecha de mantenimiento $fechaCita en horario $horaCita, esta próximo por ser asignado, recuerda contarle al usuario que debe permanecer un mayor de edad durante la visita";
                } else if (strtoupper($recordset_click[0]['Name']) === 'FINALIZADO' || strtoupper($recordset_click[0]['Name']) === 'FINALIZADA') {
                    $recordset_click[0]['Guion'] = "Nuestro personal técnico ya realizó el mantenimiento satisfactoriamente";
                } else if (strtoupper($recordset_click[0]['Name']) === 'FALLIDA CON VISITA' || strtoupper($recordset_click[0]['Name']) === 'INCOMPLETO') {
                    $recordset_click[0]['Guion'] = "Lastimosamente no fue posible realizar el 100% del mantenimiento del pedido $pedido, el cual quedo en concepto $concepto es necesario que nuevamente contactes al cliente para agendar una nueva visita";
                } else if (strtoupper($recordset_click[0]['Name']) === 'EN SITIO') {
                    $recordset_click[0]['Guion'] = "Nuestro personal técnico ya se encuentra en el domicilio realizando el mantenimiento a tu servicio, el técnico que se encuentra prestando el servicio es: $nombreTecnico con Cedula: $cedulaTecnico";
                } else if (strtoupper($recordset_click[0]['Name']) === 'EN CAMINO') {
                    $recordset_click[0]['Guion'] = "Tu mantenimiento con pedido $pedido tiene fecha de instalación $fechaCita en horario $horaCita, ya cuenta con un técnico, asignado y se dirige a la dirección, recuerda contarle al usuario que debe permanecer un mayor de edad durante la visita.";
                } else if (strtoupper($recordset_click[0]['Name']) === 'ACEPTADO') {
                    $recordset_click[0]['Guion'] = "Tu orden con pedido $pedido tiene fecha de mantenimiento $fechaCita en horario $horaCita, ya cuenta con un técnico, asignado, recuerda contarle al usuario que debe permanecer un mayor de edad durante la visita.";
                } else if (strtoupper($recordset_click[0]['Name']) === 'FALLIDO SIN VISITA' || strtoupper($recordset_click[0]['Name']) === 'PENDIENTE') {
                    $recordset_click[0]['Guion'] = "Lastimosamente no hemos logrado realizar el mantenimiento programado, el pedido $pedido se encuentra pendiente y estamos realizando la gestión necesaria para cumplir la orden.";
                } else if (strtoupper($recordset_click[0]['Name']) === 'AGENDA FUTURA') {
                    $recordset_click[0]['Guion'] = "Tu orden con pedido $pedido, no se encuentra en ruta para el día de hoy, su fecha de instalación es el $fechaCita en horario $horaCita";
                } else if (strtoupper($recordset_click[0]['Name']) === 'AGENDA VENCIDA') {
                    $recordset_click[0]['Guion'] = "El pedido que consultas es un pedido con agenda vencida, debes buscarlo en esta misma pagina, opcion <strong>Visitas terreno</strong>";
                } else {
                    $recordset_click[0]['Guion'] = "Estado desconocido";
                }
            } else {
                if (strtoupper($recordset_click[0]['Name']) === 'ASIGNADO') {
                    $recordset_click[0]['Guion'] = "Tu orden con pedido $pedido tiene fecha de instalación $fechaCita en horario $horaCita, ya cuenta con un técnico, asignado, recuerda contarle al usuario que debe permanecer un mayor de edad y contar con los equipos disponibles para la instalación.";
                } else if (strtoupper($recordset_click[0]['Name']) === 'DESPACHADO') {
                    $recordset_click[0]['Guion'] = "Tu orden con pedido $pedido tiene fecha de instalación $fechaCita en horario $horaCita, ya cuenta con un técnico, asignado, recuerda contarle al usuario que debe permanecer un mayor de edad y contar con los equipos disponibles para la instalación";
                } else if (strtoupper($recordset_click[0]['Name']) === 'SUSPENDIDA') {
                    $recordset_click[0]['Guion'] = "El pedido $pedido se ha asociado a una falla masiva, sugerimos que contactes al cliente para infórmale la situación";
                } else if (strtoupper($recordset_click[0]['Name']) === 'ABIERTO') {
                    $recordset_click[0]['Guion'] = "Tu orden con pedido $pedido tiene fecha de instalación $fechaCita en horario $horaCita, esta próximo por ser asignado, recuerda contarle al usuario que debe permanecer un mayor de edad y contar con los equipos disponibles para la instalación";
                } else if (strtoupper($recordset_click[0]['Name']) === 'FINALIZADO' || strtoupper($recordset_click[0]['Name']) === 'FINALIZADA') {
                    $recordset_click[0]['Guion'] = "Nuestro personal técnico ya realizó la instalación satisfactoriamente";
                } else if (strtoupper($recordset_click[0]['Name']) === 'FALLIDA CON VISITA' || strtoupper($recordset_click[0]['Name']) === 'INCOMPLETO') {
                    $recordset_click[0]['Guion'] = "Lastimosamente no fue posible realizar el 100% de la instalación del pedido $pedido, el cual quedo en concepto $concepto es necesario que nuevamente contactes al cliente y realizarle la gestión comercial.";
                } else if (strtoupper($recordset_click[0]['Name']) === 'EN SITIO') {
                    $recordset_click[0]['Guion'] = "Nuestro personal técnico ya se encuentra en el domicilio realizando la instalación, el técnico que se encuentra prestando el servicio es: $nombreTecnico con Cedula: $cedulaTecnico";
                } else if (strtoupper($recordset_click[0]['Name']) === 'EN CAMINO') {
                    $recordset_click[0]['Guion'] = "Tu orden con pedido $pedido tiene fecha de instalación $fechaCita en horario $horaCita, ya cuenta con un técnico, asignado y se dirige a la dirección, recuerda contarle al usuario que debe permanecer un mayor de edad y contar con los equipos disponibles para la instalación";
                } else if (strtoupper($recordset_click[0]['Name']) === 'ACEPTADO') {
                    $recordset_click[0]['Guion'] = "Tu orden con pedido $pedido tiene fecha de instalación $fechaCita en horario $horaCita, ya cuenta con un técnico, asignado, recuerda contarle al usuario que debe permanecer un mayor de edad y contar con los equipos disponibles para la instalación";
                } else if (strtoupper($recordset_click[0]['Name']) === 'FALLIDO SIN VISITA' || strtoupper($recordset_click[0]['Name']) === 'PENDIENTE') {
                    $recordset_click[0]['Guion'] = "Lastimosamente no hemos logrado realizar de la instalación, el pedido $pedido se encuentra pendiente y estamos realizando la gestión necesaria para cumplir la orden.";
                } else if (strtoupper($recordset_click[0]['Name']) === 'AGENDA FUTURA') {
                    $recordset_click[0]['Guion'] = "Tu orden con pedido $pedido, no se encuentra en ruta para el día de hoy, su fecha de instalación es el $fechaCita en horario $horaCita";
                } else if (strtoupper($recordset_click[0]['Name']) === 'AGENDA VENCIDA') {
                    $recordset_click[0]['Guion'] = "El pedido que consultas es un pedido con agenda vencida, debes buscarlo en esta misma pagina, opcion <strong>Visitas terreno</strong>";
                } else {
                    $recordset_click[0]['Guion'] = "Estado desconocido";
                }
            }

            return $recordset_click;
            
        } catch (\Exception $e) {
            //throw $e;
        }
    }

    public function validacionSalesForce($recordset_sales_force) {
        try {

            $recordset_sales_force[0]['Name'] = $this->mapper_estado($recordset_sales_force[0]['Name']); // ESTADO DE ORDEN
            $recordset_sales_force[0]['EstadoPedido'] = ($recordset_sales_force[0]['EstadoPedido'] === '-1') ? '-1' : $this->mapper_estado($recordset_sales_force[0]['EstadoPedido']); //Amarilla
            $horaCita = $recordset_sales_force[0]['HoraCita'];
            $franjaHorario = $this->mapperTimeSalesForce($recordset_sales_force[0]['HoraCita'], $recordset_sales_force[0]['HoraCitaFin']);
            $recordset_sales_force[0]['HoraCita'] = $franjaHorario;
            $recordset_sales_force[0]['hasDefMicroZona'] = preg_match('/DEF_/', $recordset_sales_force[0]['MicroZona']);
            $recordset_sales_force[0]['ObsTecnico'] = ($recordset_sales_force[0]['ObsTecnico'] === null || $recordset_sales_force[0]['ObsTecnico'] === '') ? 'Para este pedido el técnico no registro observaciones' : $recordset_sales_force[0]['ObsTecnico'];
            $recordset_sales_force[0]['CedulaCliente'] = preg_replace('/COB2C/', '', $recordset_sales_force[0]['CedulaCliente']);

            $fecha = date('Y-m-d');

            /* if ($recordset_sales_force[0]['FechaCita'] > $fecha && ($recordset_sales_force[0]['Name'] == 'Abierto' || $recordset_sales_force[0]['Name'] == 'Asignado')) {
                $recordset_sales_force[0]['Name'] = 'Agenda Futura';
            } else if ($recordset_sales_force[0]['FechaCita'] < $fecha) {
                $recordset_sales_force[0]['Name'] = 'Agenda Vencida';
            } */

            $recordset_sales_force[0]['Guion'] = '';
            $pedido = $recordset_sales_force[0]['Pedido'];
            $fechaCita = $recordset_sales_force[0]['FechaCita'];
            $nombreTecnico = $recordset_sales_force[0]['NombreTecnico'];
            $cedulaTecnico = $recordset_sales_force[0]['CedulaTecnico'];
            $concepto = $recordset_sales_force[0]['Concepto'];

            $segmento = $recordset_sales_force[0]['Segmento'];

            if (preg_match('/Aseguramiento/', $segmento)) {
                if (strtoupper($recordset_sales_force[0]['Name']) === 'ASIGNADO') {
                    $recordset_sales_force[0]['Guion'] = "Tu orden con pedido $pedido tiene fecha de mantenimiento $fechaCita en horario $franjaHorario, ya cuenta con un técnico, asignado, recuerda contarle al usuario que debe permanecer un mayor de edad durante la visita.";
                } else if (strtoupper($recordset_sales_force[0]['Name']) === 'DESPACHADO') {
                    $recordset_sales_force[0]['Guion'] = "Tu orden con pedido $pedido tiene fecha de mantenimiento $fechaCita en horario $franjaHorario, ya cuenta con un técnico, asignado, recuerda contarle al usuario que debe permanecer un mayor de edad durante la visita.";
                } else if (strtoupper($recordset_sales_force[0]['Name']) === 'SUSPENDIDA') {
                    $recordset_sales_force[0]['Guion'] = "El pedido $pedido se ha asociado a una falla masiva, sugerimos que contactes al cliente para infórmale la situación";
                } else if (strtoupper($recordset_sales_force[0]['Name']) === 'ABIERTO') {
                    $recordset_sales_force[0]['Guion'] = "Tu orden con pedido $pedido tiene fecha de mantenimiento $fechaCita en horario $franjaHorario, esta próximo por ser asignado, recuerda contarle al usuario que debe permanecer un mayor de edad durante la visita";
                } else if (strtoupper($recordset_sales_force[0]['Name']) === 'FINALIZADO' || strtoupper($recordset_sales_force[0]['Name']) === 'FINALIZADA') {
                    $recordset_sales_force[0]['Guion'] = "Nuestro personal técnico ya realizó el mantenimiento satisfactoriamente";
                } else if (strtoupper($recordset_sales_force[0]['Name']) === 'FALLIDA CON VISITA' || strtoupper($recordset_sales_force[0]['Name']) === 'INCOMPLETO') {
                    $recordset_sales_force[0]['Guion'] = "Lastimosamente no fue posible realizar el 100% del mantenimiento del pedido $pedido, el cual quedo en concepto $concepto es necesario que nuevamente contactes al cliente para agendar una nueva visita";
                } else if (strtoupper($recordset_sales_force[0]['Name']) === 'EN SITIO') {
                    $recordset_sales_force[0]['Guion'] = "Nuestro personal técnico ya se encuentra en el domicilio realizando el mantenimiento a tu servicio, el técnico que se encuentra prestando el servicio es: $nombreTecnico con Cedula: $cedulaTecnico";
                } else if (strtoupper($recordset_sales_force[0]['Name']) === 'EN CAMINO') {
                    $recordset_sales_force[0]['Guion'] = "Tu mantenimiento con pedido $pedido tiene fecha de instalación $fechaCita en horario $franjaHorario, ya cuenta con un técnico, asignado y se dirige a la dirección, recuerda contarle al usuario que debe permanecer un mayor de edad durante la visita.";
                } else if (strtoupper($recordset_sales_force[0]['Name']) === 'ACEPTADO') {
                    $recordset_sales_force[0]['Guion'] = "Tu orden con pedido $pedido tiene fecha de mantenimiento $fechaCita en horario $franjaHorario, ya cuenta con un técnico, asignado, recuerda contarle al usuario que debe permanecer un mayor de edad durante la visita.";
                } else if (strtoupper($recordset_sales_force[0]['Name']) === 'FALLIDO SIN VISITA' || strtoupper($recordset_sales_force[0]['Name']) === 'PENDIENTE') {
                    $recordset_sales_force[0]['Guion'] = "Lastimosamente no hemos logrado realizar el mantenimiento programado, el pedido $pedido se encuentra pendiente y estamos realizando la gestión necesaria para cumplir la orden.";
                } else if (strtoupper($recordset_sales_force[0]['Name']) === 'AGENDA FUTURA') {
                    $recordset_sales_force[0]['Guion'] = "Tu orden con pedido $pedido, no se encuentra en ruta para el día de hoy, su fecha de instalación es el $fechaCita en horario $horaCita";
                } else if (strtoupper($recordset_sales_force[0]['Name']) === 'AGENDA VENCIDA') {
                    $recordset_sales_force[0]['Guion'] = "El pedido que consultas es un pedido con agenda vencida, debes buscarlo en esta misma pagina, opcion <strong>Visitas terreno</strong>";
                } else {
                    $recordset_sales_force[0]['Guion'] = "Estado desconocido";
                }
            } else {
                if (strtoupper($recordset_sales_force[0]['Name']) === 'ASIGNADO') {
                    $recordset_sales_force[0]['Guion'] = "Tu orden con pedido $pedido tiene fecha de instalación $fechaCita en horario $franjaHorario, ya cuenta con un técnico, asignado, recuerda contarle al usuario que debe permanecer un mayor de edad y contar con los equipos disponibles para la instalación.";
                } else if (strtoupper($recordset_sales_force[0]['Name']) === 'DESPACHADO') {
                    $recordset_sales_force[0]['Guion'] = "Tu orden con pedido $pedido tiene fecha de instalación $fechaCita en horario $franjaHorario, ya cuenta con un técnico, asignado, recuerda contarle al usuario que debe permanecer un mayor de edad y contar con los equipos disponibles para la instalación";
                } else if (strtoupper($recordset_sales_force[0]['Name']) === 'SUSPENDIDA') {
                    $recordset_sales_force[0]['Guion'] = "El pedido $pedido se ha asociado a una falla masiva, sugerimos que contactes al cliente para infórmale la situación";
                } else if (strtoupper($recordset_sales_force[0]['Name']) === 'ABIERTO') {
                    $recordset_sales_force[0]['Guion'] = "Tu orden con pedido $pedido tiene fecha de instalación $fechaCita en horario $franjaHorario, esta próximo por ser asignado, recuerda contarle al usuario que debe permanecer un mayor de edad y contar con los equipos disponibles para la instalación";
                } else if (strtoupper($recordset_sales_force[0]['Name']) === 'FINALIZADO' || strtoupper($recordset_sales_force[0]['Name']) === 'FINALIZADA') {
                    $recordset_sales_force[0]['Guion'] = "Nuestro personal técnico ya realizó la instalación satisfactoriamente";
                } else if (strtoupper($recordset_sales_force[0]['Name']) === 'FALLIDA CON VISITA' || strtoupper($recordset_sales_force[0]['Name']) === 'INCOMPLETO') {
                    $recordset_sales_force[0]['Guion'] = "Lastimosamente no fue posible realizar el 100% de la instalación del pedido $pedido, el cual quedo en concepto $concepto es necesario que nuevamente contactes al cliente y realizarle la gestión comercial.";
                } else if (strtoupper($recordset_sales_force[0]['Name']) === 'EN SITIO') {
                    $recordset_sales_force[0]['Guion'] = "Nuestro personal técnico ya se encuentra en el domicilio realizando la instalación, el técnico que se encuentra prestando el servicio es: $nombreTecnico con Cedula: $cedulaTecnico";
                } else if (strtoupper($recordset_sales_force[0]['Name']) === 'EN CAMINO') {
                    $recordset_sales_force[0]['Guion'] = "Tu orden con pedido $pedido tiene fecha de instalación $fechaCita en horario $franjaHorario, ya cuenta con un técnico, asignado y se dirige a la dirección, recuerda contarle al usuario que debe permanecer un mayor de edad y contar con los equipos disponibles para la instalación";
                } else if (strtoupper($recordset_sales_force[0]['Name']) === 'ACEPTADO') {
                    $recordset_sales_force[0]['Guion'] = "Tu orden con pedido $pedido tiene fecha de instalación $fechaCita en horario $franjaHorario, ya cuenta con un técnico, asignado, recuerda contarle al usuario que debe permanecer un mayor de edad y contar con los equipos disponibles para la instalación";
                } else if (strtoupper($recordset_sales_force[0]['Name']) === 'FALLIDO SIN VISITA' || strtoupper($recordset_sales_force[0]['Name']) === 'PENDIENTE') {
                    $recordset_sales_force[0]['Guion'] = "Lastimosamente no hemos logrado realizar de la instalación, el pedido $pedido se encuentra pendiente y estamos realizando la gestión necesaria para cumplir la orden.";
                } else if (strtoupper($recordset_sales_force[0]['Name']) === 'AGENDA FUTURA') {
                    $recordset_sales_force[0]['Guion'] = "Tu orden con pedido $pedido, no se encuentra en ruta para el día de hoy, su fecha de instalación es el $fechaCita en horario $horaCita";
                } else if (strtoupper($recordset_sales_force[0]['Name']) === 'AGENDA VENCIDA') {
                    $recordset_sales_force[0]['Guion'] = "El pedido que consultas es un pedido con agenda vencida, debes buscarlo en esta misma pagina, opcion <strong>Visitas terreno</strong>";
                } else {
                    $recordset_sales_force[0]['Guion'] = "Estado desconocido";
                }
            }
            


            return $recordset_sales_force;
            
        } catch (\Exception $e) {
            //throw $e;
        }
    }


    public function mapperTimeSalesForce($horaInicioCita, $horaFinCita)
    {
        if ($horaInicioCita = '06:00:00' && $horaFinCita == '12:00:00') { return 'AM'; }
        if ($horaInicioCita = '07:00:00' && $horaFinCita == '07:51:00') { return 'AM'; }
        if ($horaInicioCita = '07:00:00' && $horaFinCita == '10:00:00') { return 'AM'; }
        if ($horaInicioCita = '07:00:00' && $horaFinCita == '10:30:00') { return 'AM'; }
        if ($horaInicioCita = '07:00:00' && $horaFinCita == '12:00:00') { return 'AM'; }
        if ($horaInicioCita = '07:08:00' && $horaFinCita == '11:09:00') { return 'AM'; }
        if ($horaInicioCita = '08:00:00' && $horaFinCita == '11:59:00') { return 'AM'; }
        if ($horaInicioCita = '09:00:00' && $horaFinCita == '10:00:00') { return 'AM'; }
        if ($horaInicioCita = '09:00:00' && $horaFinCita == '12:00:00') { return 'AM'; }
        if ($horaInicioCita = '09:00:00' && $horaFinCita == '12:30:00') { return 'AM'; }
        if ($horaInicioCita = '10:00:00' && $horaFinCita == '12:00:00') { return 'AM'; }
        if ($horaInicioCita = '10:00:00' && $horaFinCita == '14:00:00') { return 'AM'; }
        if ($horaInicioCita = '10:30:00' && $horaFinCita == '11:59:00') { return 'AM'; }
        if ($horaInicioCita = '10:30:00' && $horaFinCita == '12:30:00') { return 'AM'; }
        if ($horaInicioCita = '11:00:00' && $horaFinCita == '12:00:00') { return 'AM'; }
        if ($horaInicioCita = '11:28:00' && $horaFinCita == '12:00:00') { return 'AM'; }
        if ($horaInicioCita = '12:00:00' && $horaFinCita == '12:45:00') { return 'PM'; }
        if ($horaInicioCita = '12:00:00' && $horaFinCita == '13:00:00') { return 'PM'; }
        if ($horaInicioCita = '12:00:00' && $horaFinCita == '13:30:00') { return 'PM'; }
        if ($horaInicioCita = '12:00:00' && $horaFinCita == '15:00:00') { return 'PM'; }
        if ($horaInicioCita = '12:00:00' && $horaFinCita == '16:00:00') { return 'PM'; }
        if ($horaInicioCita = '12:00:00' && $horaFinCita == '17:00:00') { return 'PM'; }
        if ($horaInicioCita = '12:00:00' && $horaFinCita == '18:00:00') { return 'PM'; }
        if ($horaInicioCita = '12:30:00' && $horaFinCita == '15:00:00') { return 'PM'; }
        if ($horaInicioCita = '12:30:00' && $horaFinCita == '15:30:00') { return 'PM'; }
        if ($horaInicioCita = '13:00:00' && $horaFinCita == '15:00:00') { return 'PM'; }
        if ($horaInicioCita = '13:00:00' && $horaFinCita == '16:00:00') { return 'PM'; }
        if ($horaInicioCita = '13:00:00' && $horaFinCita == '18:00:00') { return 'PM'; }
        if ($horaInicioCita = '14:00:00' && $horaFinCita == '16:30:00') { return 'PM'; }
        if ($horaInicioCita = '15:00:00' && $horaFinCita == '17:00:00') { return 'PM'; }
        if ($horaInicioCita = '15:00:00' && $horaFinCita == '18:00:00') { return 'PM'; }
        if ($horaInicioCita = '15:30:00' && $horaFinCita == '17:30:00') { return 'PM'; }
        if ($horaInicioCita = '15:30:00' && $horaFinCita == '17:45:00') { return 'PM'; }
        if ($horaInicioCita = '00:00:00' && $horaFinCita == '15:30:00') { return 'TD'; }
        if ($horaInicioCita = '00:00:00' && $horaFinCita == '23:59:00') { return 'TD'; }
        if ($horaInicioCita = '07:00:00' && $horaFinCita == '23:45:00') { return 'TD'; }
        if ($horaInicioCita = '08:00:00' && $horaFinCita == '17:00:00') { return 'TD'; }
        if ($horaInicioCita = '12:00:00' && $horaFinCita == '12:00:00') { return 'TD'; }
        return 'TD';
    }



    public function equiposInstalados($data)
    {
        require_once 'Contador.php';
        $contador = new Contador();
        $contador->Contador('Consulta-equipos-instalados');

        $pedido = $data['pedido'];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://10.100.66.254/visitas-terreno/api/equipos/$pedido");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $data = curl_exec($ch);
        curl_close($ch);
        $dataclick = json_decode($data, TRUE);
        echo json_encode($dataclick);
    }

    public function incompleto($data)
    {
        $pedido = $data['pedido'];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://10.100.66.254/visitas-terreno/api/incompleto/$pedido");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $data = curl_exec($ch);
        curl_close($ch);
        $dataclick = json_decode($data, TRUE);
        echo json_encode($dataclick);
    }

    public function Supervisor($data)
    {
        $cedula = $data['cedula'];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://10.100.66.254/visitas-terreno/api/supervisor/$cedula");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $data = curl_exec($ch);
        curl_close($ch);
        $dataclick = json_decode($data, TRUE);
        echo json_encode($dataclick);
    }

}
