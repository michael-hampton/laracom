<!doctype html>
<html>
    <head>
        @include('includes.head')
    </head>
    <body>

        <div id="wrapper">

            <nav class="navbar-default navbar-static-side" role="navigation">

                @include('includes.sidebar')
            </nav>

            <div id="page-wrapper" class="gray-bg" style="min-height: 1984px;">

                <div class="row border-bottom">
                    <nav class="navbar navbar-static-top" role="navigation" style="margin-bottom: 0">
                        @include('includes.header')
                    </nav>
                </div>

                @yield('content')
            </div>

            <footer class="row">
                @include('includes.footer')
            </footer>

        </div>
    </body>
</html>