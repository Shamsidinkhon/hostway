<?php

use \Phalcon\Http\Response;

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
    return responseHandle($model->getAll($this->request->getQuery()));
});

// Retrieves phone book based on primary key
$app->get(
    '/api/phone-books/{id:[0-9]+}',
    function ($id) {
        $model = PhoneBooks::find($id)->toArray();
        if (!$model)
            return responseHandle(['status' => false, 'data' => []]);
        return responseHandle(['status' => true, 'data' => $model]);
    }
);


// Adds a new phone book
$app->post(
    '/api/phone-books',
    function () {
        $model = new PhoneBooks();
        $model->assign($this->request->getJsonRawBody(true));
        if ($model->save())
            return responseHandle(['status' => true, 'data' => []]);
        else
            return responseHandle(['status' => false, 'data' => $model->getMessages()]);
    }
);

// Updates phone book based on primary key
$app->put(
    '/api/phone-books/{id:[0-9]+}',
    function ($id) {
        $model = PhoneBooks::find($id);
        if(!$model)
            return responseHandle(['status' => false, 'data' => "Item Not Found"]);
        if ($model->update($this->request->getJsonRawBody(true)))
            return responseHandle(['status' => true, 'data' => "Item Successfully Saved"]);
        else
            return responseHandle(['status' => false, 'data' => $model->getMessages()]);
    }
);

// Deletes phone book based on primary key
$app->delete(
    '/api/phone-books/{id:[0-9]+}',
    function ($id) {
        $model = PhoneBooks::find($id);
        if(!$model)
            return responseHandle(['status' => false, 'data' => "Item Not Found"]);
        return responseHandle(['status' => $model->delete(), 'data' => []]);
    }
);

/**
 * Not found handler
 */
$app->notFound(function () use ($app) {
    $app->response->setStatusCode(404, "Not Found")->sendHeaders();
    return responseHandle(['status' => false, 'data' => "404 Not Found"]);
});

/**
 * Response handler
 * @param array $data
 */
function responseHandle($data)
{
    $response = new Response();
    if (is_array($data))
        $response->setJsonContent($data);
    else
        $response->setJsonContent(['status' => false, 'data' => 'Something went wrong!']);
    return $response;
}
