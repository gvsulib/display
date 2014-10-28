<?php
session_start();
if ($_GET['logout']){
    $_SESSION = array();
    session_destroy();
}
if ($_SESSION['loggedIn'] != true){
    header('location: login.php');
}
require 'LoggerControl.php';
$cfg = LoggerConfig::get();
$obj = new LoggerControl();
$list = $obj->getList();
if (!$cfg || !is_array($list)) return;

?><!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>JS LogFlush Manager</title>

        <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">
        <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap-theme.min.css">
        <link rel="stylesheet" type="text/css" href="manager.css" />
    </head>

    <body>

        <div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
            <div class="container-fluid">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" href="#about" data-toggle="modal">JS LogFlush Manager</a>
                </div>
                <div class="navbar-collapse collapse">
                    <form class="navbar-form navbar-left" id="controls" onsubmit="return false">
                        <input type="text" class="form-control" placeholder="New web app" title="Press &lt;Enter&gt; to register new web app with entered URL">
                        <button type="button" class="btn btn-primary filter" title="Filter log files by IP">
                            <span class="glyphicon glyphicon-filter"></span> IP<span class="value"></span>
                        </button>
                        <button type="button" class="btn btn-default refresh" title="Refresh the list of log files">
                            <span class="glyphicon glyphicon-refresh"></span>
                        </button>
                        <button type="button" class="btn btn-default remove" title="Remove all visible log files">
                            <span class="glyphicon glyphicon-remove"></span>
                        </button>
                    </form>
                    <ul class="nav navbar-nav navbar-right">
                        <li><a href="#config" data-toggle="modal"><span class="glyphicon glyphicon-cog"></span> Config</a></li>
                        <li><a href="manager.php?logout=true">Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-4 col-md-3 sidebar">
                    <ul class="nav nav-pills nav-stacked" id="url-list"></ul>
                </div>

                <div class="col-sm-3 col-sm-offset-4 col-md-2 col-md-offset-3 sidebar">
                    <ul class="nav nav-pills nav-stacked" id="file-list"></ul>
                </div>

                <div class="col-sm-5 col-sm-offset-7 col-md-7 col-md-offset-5 main">
                    <div id="file-content"></div>
                </div>
            </div>
        </div>

        <div id="config" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="configLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">
                            <span aria-hidden="true">&times;</span><span class="sr-only">Close</span>
                        </button>
                        <h4 class="modal-title" id="configLabel">Configuration</h4>
                    </div>
                    <div class="modal-body">
                        <form id="configForm" role="form" onsubmit="return false"></form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary save">Save changes</button>
                    </div>
                </div>
            </div>
        </div>

        <div id="about" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="aboutLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">
                            <span aria-hidden="true">&times;</span><span class="sr-only">Close</span>
                        </button>
                        <h4 class="modal-title" id="aboutLabel">About</h4>
                    </div>
                    <div class="modal-body">
                        <p>This is an example of log storage manager for <a href="https://github.com/hindmost/jslogflush">JS LogFlush</a>.</p>
                        <p><strong>How to use:</strong></p>
                        <p>Plug <a href="http://demos.savreen.com/jslogflush-manager/logger.php">this processing script</a> into your web application
                        (see <a href="https://github.com/hindmost/jslogflush">README instructions</a>),
                        register the latter in the manager by entering its URL in the &quot;New web app&quot; field
                        and you can watch and manage all the data logged (by console.log calls) in your web app.</p>
                        <p><a href="https://github.com/hindmost/jslogflush-manager"><strong>Fork on GitHub</strong></a></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <script type="text/template" id="url-template">
            <a href="#" class="item url-item"><%= title %></a>
            <span class="hovertools">
                <a href="<%= id %>" class="link" title="Go to this web app">
                    <span class="glyphicon glyphicon-link"></span>
                </a>
                <a href="#remove" class="remove" title="Remove this web app">
                    <span class="glyphicon glyphicon-remove"></span>
                </a>
            </span>
        </script>

        <script type="text/template" id="file-template">
            <a href="#" class="item file-item" title="IP: <%= ip %>">
                <%= time %>
            </a>
            <span class="hovertools">
                <a href="#remove" class="remove" title="Remove this log file">
                    <span class="glyphicon glyphicon-remove"></span>
                </a>
            </span>
        </script>

        <script type="text/template" id="content-template">
            <dl class="dl-horizontal">
                <dt>Log File:</dt>
                <dd><a href="<%= path %>" title="Download link"><%= id %></a></dd>
                <dt>Web App URL:</dt>
                <dd><a href="<%= url %>"><%= url %></a></dd>
                <dt>Client&#039;s IP:</dt>
                <dd><%= ip %></dd>
                <dt>UserAgent:</dt>
                <dd><%= useragent %></dd>
                <dt>Session started:</dt>
                <dd><%= time %></dd>
            </dl>
            <pre class="pre-scrollable"><%= content %></pre>
        </script>

        <script type="text/template" id="config-template">
            <div class="form-group">
                <label>Log buffer size, bytes</label>
                <input type="text" class="form-control" name="buff_size" value="<%= buff_size %>">
            </div>
            <div class="form-group">
                <label>Log flush interval, secs</label>
                <input type="text" class="form-control" name="interval" value="<%= interval %>">
            </div>
            <div class="form-group">
                <label>Background flush interval, secs</label>
                <input type="text" class="form-control" name="interval_bk" value="<%= interval_bk %>">
            </div>
            <div class="form-group">
                <label>Log session expiration time, hours</label>
                <input type="text" class="form-control" name="expire" value="<%= expire %>">
            </div>
            <div class="form-group">
                <label>Limit for requests per flush interval (0 - no limit)</label>
                <input type="text" class="form-control" name="requests_limit" value="<%= requests_limit %>">
            </div>
            <div class="form-group">
                <label>Include timeshift into each log record</label>
                <select class="form-control" name="log_timeshifts">
                    <option value="0" <%= log_timeshifts? '' : 'selected' %>>No</option>
                    <option value="1" <%= log_timeshifts? 'selected' : '' %>>Yes</option>
                </select>
            </div>
            <div class="form-group">
                <label>Substitute console object with logflush</label>
                <select class="form-control" name="subst_console">
                    <option value="0" <%= subst_console? '' : 'selected' %>>No</option>
                    <option value="1" <%= subst_console? 'selected' : '' %>>Yes</option>
                </select>
            </div>
            <div class="form-group">
                <label>Minify result JS script</label>
                <select class="form-control" name="minify">
                    <option value="0" <%= minify? '' : 'selected' %>>No</option>
                    <option value="1" <%= minify? 'selected' : '' %>>Yes</option>
                </select>
            </div>
        </script>

        <script type="text/javascript" src="//code.jquery.com/jquery-1.11.1.min.js"></script>
        <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/underscore.js/1.5.2/underscore-min.js"></script>
        <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/backbone.js/1.1.2/backbone-min.js"></script>
        <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>

        <script type="text/javascript" src="manager.js"></script>
        <script type="text/javascript">
$(function() {
    var deftCfg = <?= json_encode($cfg) ?>;
    var deftFilelist = <?= json_encode($list) ?>;
    manager('logger_cfg.php', 'logger_ctl.php', deftCfg, deftFilelist);
});
        </script>

    </body>
</html>
