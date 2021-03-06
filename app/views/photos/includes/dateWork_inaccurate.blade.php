<!doctype html>
<html>
<head>
  <meta charset="utf8">
  <link rel="stylesheet" type="text/css" href="{{ URL::to("/") }}/css/style.old.css" />
  <script type="text/javascript" src="{{ URL::to('/js/jquery.min.js') }}"></script>
  <script type="text/javascript" src="{{ URL::to('/js/inputmask.min.js') }}"></script>
  <script type="text/javascript" src="{{ URL::to('/js/jquery.inputmask.min.js') }}"></script>
  <script type="text/javascript" src="{{ URL::to('/js/photo.js') }}"></script>
  <script src="{{ URL::to('/js/jquery.tooltipster.min.js') }}"></script>
</head>
<body>
  <div class="container">
    <?php $date_field = 'workdate' ?>
    <div class="twelve columns">
      <p>
        <label for="{{ $date_field }}">
          Data da Obra:
        </label>
        <img src="{{ URL::to('/img/Help-14.png') }}" class="date_help"/>
      </p>
    </div>
    @include('photos.includes.datepicker_include')
    
  </div>
  <style>
    div.container {
      margin-top: 5px;
    }

  </style>
</body>
</html>