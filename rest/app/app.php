<?php

/**
 * Local variables
 * @var \Phalcon\Mvc\Micro $app
 */

/**
 * Add your routes here
 */

// Retrieves all phone books
$app->get('/api/phone-books', function () {
    $model = new PhoneBooks();
    return $model->getAll($this->request->getQuery());
});

// Retrieves phone book based on primary key
$app->get(
    '/api/phone-books/{id:[0-9]+}',
    function ($id) {
        $model = PhoneBooks::find($id)->toArray();
        if (!$model)
            return ['status' => false, 'data' => []];
        return ['status' => true, 'data' => $model];
    }
);


// Adds a new phone book
$app->post(
    '/api/phone-books',
    function () {
        $model = new PhoneBooks();
        $model->assign($this->request->getJsonRawBody(true));
        if ($model->save())
            return ['status' => true, 'data' => []];
        else
            return ['status' => false, 'data' => $model->getMessages()];
    }
);

// Updates phone book based on primary key
$app->put(
    '/api/phone-books/{id:[0-9]+}',
    function ($id) use ($app) {
        $model = PhoneBooks::find($id)->getFirst();
        if(!$model)
            return ['status' => false, 'data' => "Item Not Found"];

        if ($model->assign($app->request->getJsonRawBody(true)) && $model->update())
            return ['status' => true, 'data' => "Item Successfully Saved"];
        else
            return ['status' => false, 'data' => $model->getMessages()];
    }
);

// Deletes phone book based on primary key
$app->delete(
    '/api/phone-books/{id:[0-9]+}',
    function ($id) {
        $model = PhoneBooks::find($id)->getFirst();
        if(!$model)
            return ['status' => false, 'data' => "Item Not Found"];
        return ['status' => $model->delete(), 'data' => []];
    }
);

/**
 * Not found handler
 */
$app->notFound(function () use ($app) {
    $app->response->setStatusCode(404, "Not Found")->sendHeaders();
    return ['status' => false, 'data' => "404 Not Found"];
});

$app->error(
    function ($e) use ($app) {
        $app->logger->critical($e->getMessage() . '<br>' . '<pre>' . $e->getTraceAsString() . '</pre>');
        return [
            'status' => false,
            'data' => 'Something went wrong. The issue is being fixed right now!'
        ];
    }
);
