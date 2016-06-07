<!DOCTYPE html>
<html>
    <head>
        <title>Module {MODULE_NAME}</title>

        <link href="https://fonts.googleapis.com/css?family=Lato:100" rel="stylesheet" type="text/css">
        <link href={!! url("{ASSETS_URL}app.css") !!} rel="stylesheet" type="text/css">

    </head>
    <body>
        <div class="container">
            <div class="content">
                <div class="title">Laravel 5 Modular</div>
                <div class="title module">{MODULE_NAME} Module</div>
                <div class="subtitle">@lang('{MODULE_NAME}::test.lorem')</div>
                <small>Config file test -> Value of foo is '{{$foo}}'</small>
                <br>
                <small>Hook test -> {!! \Lucid\Modular\ModulesManager::getHook('{MODULE_NAME}.attachPoint1') !!}</small>
            </div>
        </div>
    </body>
</html>
