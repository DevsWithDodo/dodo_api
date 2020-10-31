<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $e)
    {
        if ($e instanceof ModelNotFoundException) {
            return response()->json(['error' => '404 Resource Not Found'], 404);
        }
        if ($this->isHttpException($e)) {
            if($e->getStatusCode() == '404'){
                return response()->json(['error' => '404 Not Found'], 404);
            }
        }

        // Define the response
        $response = [
            'error' => '500 Server Error'
        ];

        // If the app is in debug mode
        if (config('app.debug')) {
            // Add the exception class name, message and stack trace to response
            $response['exception'] = get_class($e); // Reflection might be better here
            $response['message'] = $e->getMessage();
            $response['trace'] = $e->getTrace();
        }

        // Default response of 400
        $status = 400;

        // If this exception is an instance of HttpException
        if ($this->isHttpException($e)) {
            // Grab the HTTP status code from the Exception
            $status = $e->getStatusCode();
        }

        // Return a JSON response with the response array and status code
        return response()->json($response, $status);
    }
}
