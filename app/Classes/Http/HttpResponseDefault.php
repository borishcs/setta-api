<?php
namespace App\Classes\Http;

class HttpResponseDefault
{
    private function mountResponse($message, $field, $statusCode)
    {        
        return response()->json(
            [
                'errors' => [
                    $field =>
                        $message,
                ],
            ],
            $statusCode
        );
    }

    private function mountGenericResponse($message, $statusCode)
    {        
        return response()->json(['message' => $message,], $statusCode);
    }

    public function setResponse($msgError, $field, $statusCode) {
        return $this->mountResponse(
            $msgError,
            $field,
            $statusCode
        );
    }

    public function setGenericResponse($msgError, $statusCode) {
        return $this->mountGenericResponse(
            $msgError,
            $statusCode
        );
    }
}
