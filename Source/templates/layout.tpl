<!doctype html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Slim Base Api</title>

    <link rel="stylesheet" href="/assets/style.css?v={'now' | date : 'YmdHis'}">
</head>

<body>

<div class="container">
    <h1>{$title}</h1>
</div>
<div class="container">
    <p>{$message}</p>
    <p>Checkout <a href="/info">phpinfo()</a></p>
    <p>Also try echo <a href="/echo/EchoValue">/echo/EchoValue</a></p>
</div>
</body>

</html>