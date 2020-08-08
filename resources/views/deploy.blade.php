<?php
/** @var \Illuminate\Support\Collection $commits */
/** @var \Illuminate\Support\Collection $sources */
/** @var int $currentPage */
/** @var string $output */
/** @var int $prodCommit */
?>
<!doctype html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="//cdn.elnu.de/libs/twitter-bootstrap/4.3.1/css/bootstrap.min.css" crossorigin="anonymous">

    <title>{{ config('app.name') }}</title>
</head>
<body>

<div class="container">
    <div class="py-5 text-center">
        <img class="d-block mx-auto mb-4" src="{{ config('deployment.app_logo') }}" alt="" width="72" height="72">
        <h2>Update {{ config('app.name') }} Sources</h2>
        <p class="lead">The fastest way to update {{ config('app.name') }}.</p>
    </div>

    <div class="row">

        <div class="col-md-6 order-md-2 mb-4">
            <h4 class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-muted">Latest Commints</span>
            </h4>
            <ul class="list-group mb-3">
                @foreach($commits as $commit)

                @php
                    $commitHash = substr($commit->hash,0, 7);
                    if ($prodCommit == $commitHash) {
                        $commitHtml = "<a href='{$commit->links->html->href}' style='color: darkred; font-weight: bolder; background: orange' target='_blank'>".$commitHash."</a>";
                    } else {
                        $commitHtml = "<a href='{$commit->links->html->href}' target='_blank'>".$commitHash."</a>";
                    }
                @endphp

                <li class="list-group-item d-flex justify-content-between lh-condensed">
                    <div class="{!! count($commit->parents) > 1 ? 'text-success' : '' !!}">
                        <h6 class="my-0">{{ $commit->message }}</h6>
                        <small class="text-muted">
                            @if(isset($commit->author->user))
                                {{ $commit->author->user->display_name }}
                            @else
                                {{ $commit->author->raw }}
                            @endif

                            {{ \Illuminate\Support\Carbon::parse($commit->date)->format('D Y-m-d H:i:s') }}
                        </small>
                    </div>
                    <span class="text-muted">{!! $commitHtml !!}</span>
                </li>
                @endforeach
            </ul>
            <nav aria-label="Page navigation example">
                <ul class="pagination">
                    @if($currentPage == 1)
                        <li class="page-item disabled">
                            <a class="page-link" href="?user={{ request()->get('user') }}&page=1">Previous</a>
                        </li>
                    @else
                        <li class="page-item">
                            <a class="page-link" href="?user={{ request()->get('user') }}&page={{ $currentPage-1 }}">Previous</a>
                        </li>
                    @endif
                    @for($i = 0;$i<4;$i++)
                        <li class="page-item {{ $currentPage == $currentPage+$i ? 'active' : '' }}">
                            <a class="page-link" href="?user={{ request()->get('user') }}&page={{ $currentPage+$i }}">{{ $currentPage+$i }}</a>
                        </li>
                    @endfor
                    <li class="page-item">
                        <a class="page-link" href="?user={{ request()->get('user') }}&page={{ $currentPage+1 }}">Next</a>
                    </li>
                </ul>
            </nav>
        </div>

        <div class="col-md-6 order-md-1">
            <h4 class="mb-3">Update Source</h4>

            @if(\App\Support\Deployer::canDeploy())
                <form class="needs-validation" novalidate method="post">
                    @csrf

                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="environment">Environment</label>
                            <select class="custom-select d-block w-100" name="environment" id="environment" required>
                                @foreach($sources as $key => $source)
                                    <option value="{{ $key }}" {{ old('environment') == $key ? 'selected' : '' }}>{{ $source }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <hr class="mb-4">

                    @if(isset($output))
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Output:</label>
                                <code>
                                    {!! $output !!}
                                </code>
                            </div>
                        </div>
                        <hr class="mb-4">
                    @endif


                    <button class="btn btn-primary btn-lg btn-block" type="submit">Update Environment</button>
                </form>
            @else
                <div class="alert alert-danger">
                    {{ __('can not deploy today') }} <br>
                    {!! \App\Support\Deployer::allowedDays()->implode(', ') !!}
                </div>
            @endif


            @if ($errors->any())
            <br>
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

        </div>

    </div>
</div>


<!-- Optional JavaScript -->
<!-- jQuery first, then Popper.js, then Bootstrap JS -->
<script src="//code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<script src="//cdn.elnu.de/libs/popper.js/1.16.0/umd/popper.min.js" crossorigin="anonymous"></script>
<script src="//cdn.elnu.de/libs/twitter-bootstrap/4.3.1/js/bootstrap.min.js" crossorigin="anonymous"></script>

</body>
</html>
