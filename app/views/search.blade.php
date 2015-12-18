@extends('layouts.default')

@section('head')

<title>Arquigrafia - Seu universo de imagens de arquitetura</title>

<!-- ISOTOPE -->
<script src="{{ URL::to("/") }}/js/jquery.isotope.min.js"></script>
<script type="text/javascript" src="{{ URL::to("/") }}/js/panel.js"></script>
<script src="{{ URL::to('/js/searchPagination.js') }}"></script> 
<link rel="stylesheet" type="text/css" href="{{ URL::to('/css/tabs.css') }}">
<link rel="stylesheet" type="text/css" href="{{ URL::to("/") }}/css/album.css" />
<link rel="stylesheet" type="text/css" href="{{ URL::to("/") }}/css/checkbox-edition.css" />
<script>

    var paginators = {
      add: {
        currentPage: 1,
        maxPage: {{ $maxPage }},
        url: '{{ $url }}',
        loadedPages: [1],
        selectedItems: 0,
        searchQuery: '',
        selected_photos: 0,
      }
    };
    var coverPage = 1;    
    var covers_counter = 0;    
    var update = null;
   
  </script>
@stop

@section('content')
  <!--   MEIO DO SITE - ÁREA DE NAVEGAÇÃO   -->
  <div id="content">

    <div class="container">
      <div id="search_result" class="twelve columns row">  
        <h1>
          @if ($city != "") 
              Resultados encontrados para: "{{ ucwords($query) }}" da cidade de "{{ucwords($city)}}"
          @elseif ( isset($binomial_option) )
            Resultados encontrados para arquiteturas com característica: 
            @if ( isset($value) )
              {{ $value }}%
            @endif
            {{ $binomial_option }}
          @else
            Resultados encontrados para: {{ $query }}
          @endif
        </h1>
       <!-- To data search  -->
        @if( count($dateFilter) != 0 )
          <p>
            Data: 
            @foreach ($dateFilter as $k => $date)
              @if ( $k != "do" )
                <a href="{{ URL::to("/search?q=".$query."&d=".$k)}}"> {{ $date }} </a>,
              @else
                <a href="{{ URL::to("/search?q=".$query."&d=".$k)}}"> {{ $date }} </a>
              @endif
            @endforeach
          </p>
        @endif
       <!-- -->
        @if( count($tags) != 0 )            
          <p style="display: inline">
            Tags contendo o termo: 
            @foreach($tags as $k => $tag)
              @if ($k != count($tags)-1 )
                <form id="{{$k}}" action="{{ URL::to("/") }}/search" method="post" accept-charset="UTF-8" style="display: inline">
                  <input type="hidden" name="q" value="{{$tag->name}}"/>
                  <a href="javascript: submitform({{$k}});">{{ $tag->name }}</a>, 
                </form>  
              @else
                <form id="{{$k}}" action="{{ URL::to("/") }}/search" method="post" accept-charset="UTF-8" style="display: inline">
                <input type="hidden" name="q" value="{{$tag->name}}"/>
                  <a href="javascript: submitform({{$k}});">{{ $tag->name }}</a>
                </form>
              @endif
            @endforeach
          </p>
          <script type="text/javascript">
            function submitform(object)
            {
              document.getElementById(object).submit();
            }
          </script>
        @endif


        @if( count($authors) != 0 )            
          <p style="display: inline">
            Autores contendo o termo: 
            @foreach($authors as $v => $author)
            
              @if ($v != count($authors)-1 )
                <form id="a{{$v}}" action="{{ URL::to("/") }}/search" method="post" accept-charset="UTF-8" style="display: inline">
                  <input type="hidden" name="q" value="{{$author->name}}"/>
                  <input type="hidden" name="type" value="a"/>
                  <a href="javascript: submitformS('a{{$v}}');">{{ $author->name }}</a>; 
                </form>  
              @else
                <form id="a{{$v}}" action="{{ URL::to("/") }}/search" method="post" accept-charset="UTF-8" style="display: inline">
                <input type="hidden" name="q" value="{{$author->name}}"/>
                <input type="hidden" name="type" value="a"/>
                  <a href="javascript: submitformS('a{{$v}}');">{{ $author->name }}</a>
                </form>
              @endif
            @endforeach
          </p>
          <script type="text/javascript">
            function submitformS(objectAuthor)
            { 
              document.getElementById(objectAuthor).submit();
            }
          </script>
        @endif


        @if ( count($photos) < 1 && !isset($binomial_option) )
          <p>Não encontramos nenhuma imagem com o termo {{ $query }}.</p>
        @elseif (count($photos) < 1)
          <p>Não foi encontrada nenhuma imagem com arquitetura classificada como
          {{ lcfirst($binomial_option) }} </p>
        @else
          <p>Foram encontradas {{ $photosAll }} imagens.</p>
        @endif
        <p>Faça uma <a href="{{ URL::to('/search/more') }}">busca avançada aqui</a>.</p>
        <p><a href="{{ URL::previous() }}">Voltar para página anterior</a></p>
      </div>
    </div>

    <!--   PAINEL DE IMAGENS - GALERIA - CARROSSEL   -->  
     <!--  <div class="wrap">
         <div id="panel">
            include('includes.panel')
        </div>
        <div class="panel-back"></div>
        <div class="panel-next"></div>
        </div> -->
    <!--   FIM - PAINEL DE IMAGENS  -->

    <!-- FOTOS PAGINADAS -->
   
    @include('includes.result-search')
  
    <!-- FIM FOTOS PAGINADAS -->
  </div>
  <!--   FIM - MEIO DO SITE   -->
    
@stop