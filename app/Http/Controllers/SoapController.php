<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Services\Soap\SoapTaskClientService;
use App\Utils\ApiResponse;
use Illuminate\Support\Facades\Log;

class SoapController extends Controller {

    public function __construct(private SoapTaskClientService $soapClient) {}
    
    /**
     * @OA\Post(
     *     path="/api/tasks/send-soap",
     *     tags={"Soap Services"},
     *     summary="Enviar tareas por SOAP",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Tareas enviadas exitosamente"),
     *     @OA\Response(response=500, description="Error al enviar tareas")
     * )
    */
    public function sendTasksToExternalSoap()
    {
    $user = auth()->guard()->id();
    $tasks = Task::where('user_id', $user)->get();

    $xml = new \SimpleXMLElement('<tasks/>');

    foreach ($tasks as $task) {
        $taskNode = $xml->addChild('task');
        $taskNode->addChild('title', htmlspecialchars($task->title));
        $taskNode->addChild('status', $task->status);
    }

    $xmlContent = $xml->asXML();

    try {
        $response = $this->soapClient->sendExportedXml($xmlContent);

        $responseXml = new \SimpleXMLElement('<response/>');
        $responseXml->addChild('status', 'success');
        $responseXml->addChild('message', 'Tareas enviadas correctamente');

        Log::info('SOAP Response:', ['response' => $response]);
        return response($responseXml->asXML(), 200)
            ->header('Content-Type', 'application/xml');
    } catch (\Exception $e) {
        $errorXml = new \SimpleXMLElement('<error/>');
        $errorXml->addChild('status', 'error');
        $errorXml->addChild('message', 'No se pudo enviar las tareas');
        $errorXml->addChild('details', htmlspecialchars($e->getMessage()));
        Log::error('SOAP Response:', ['response' => $e->getMessage()]);

        return response($errorXml->asXML(), 500)
            ->header('Content-Type', 'application/xml');
    }
}
}