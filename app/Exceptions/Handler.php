<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
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
        if ($request->is('api/*')) {
            $response = [
                'error' => "Server Error"
            ];
            $status = 500;

            if ($e instanceof ModelNotFoundException) {
                $response['error'] = "Model not found";
                $status = 404;
            }
            if ($e instanceof NotFoundHttpException) {
                $response['error'] = "404 Not found";
                $status = 404;
            }

            if ($e instanceof ValidationException) {
                $response['error'] = $e->getMessage();
                $status = 400;
            }

            if ($e instanceof AuthenticationException) {
                //unathorized
                $response['error'] = $e->getMessage();
                $status = 401;
            }

            if ($e instanceof AuthorizationException) {
                //policy
                $response['error'] = $e->getMessage();
                $status = 403;
            }

            if ($e instanceof HttpException) {
                //abort
                $response['error'] = $e->getMessage();
                $status = $e->getStatusCode();
            }

            if (config('app.debug')) {
                $response['error'] = $e->getMessage();
                $response['exception'] = get_class($e);
                $response['trace'] = $e->getTrace();
            }

            // Return a JSON response with the response array and status code
            return response()->json($response, $status);
        }
        return parent::render($request, $e);
    }
}
