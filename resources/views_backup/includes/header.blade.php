
<div class="navbar-header">
    <a class="navbar-minimalize minimalize-styl-2 btn btn-primary " href="#"><i class="fa fa-bars"></i> </a>
    <form role="search" class="navbar-form-custom" action="search_results.html">
        <div class="form-group">
            <input type="text" placeholder="Search for something..." class="form-control" name="top-search" id="top-search">
        </div>
    </form>
</div>
<ul class="nav navbar-top-links navbar-right">
    <li>
        <span class="m-r-sm text-muted welcome-message">Welcome to INSPINIA+ Admin Theme.</span>
    </li>




    <li>
        <a href="login.html">
            <i class="fa fa-sign-out"></i> <a href="{{ URL::to('logout') }}">Log out</a>
        </a>
    </li>
</ul>