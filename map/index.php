<html>
  <head>
    <title>Beer Fest Winners</title>
    <meta name="description" content="Great American Beer Festival Winningest Cities Historical Heat Map shows a timeline of city wins." />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="canonical" href="https://www.andrewvantassel.com/beerfest-winners/" />
    
    <meta property="og:title" content="GABF Winners" />
    <meta property="og:description" content="Great American Beer Festival Winningest Cities Historical Heat Map shows a timeline of city wins." />
    <meta property="og:image" content="https://www.andrewvantassel.com/beerfest-winners/screenshot.png"/>
    <meta property=”og:type” content=”website” />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" integrity="sha512-+4zCK9k+qNFUR5X+cKL9EIR+ZOhtIloNl9GIKS57V1MyNsYpYcUrUeQc9vNfzsWfV28IaLL3i96P9sdNyeRssA==" crossorigin="anonymous" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/leaflet.css" integrity="sha512-xodZBNTC5n17Xt2atTPuE1HxjVMSvLVW9ocqUKLsCC5CXdbqCmblAshOMAS6/keqq/sMZMZ19scR4PsZChSR7A==" crossorigin="anonymous" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fomantic-ui/2.8.7/semantic.min.css" integrity="sha512-g/MzOGVPy3OQ4ej1U+qe4D/xhLwUn5l5xL0Fa7gdC258ZWVJQGwsbIR47SWMpRxSPjD0tfu/xkilTy+Lhrl3xg==" crossorigin="anonymous" />
    <link rel="stylesheet" href="/map/css/semantic-ui-range.css" />
    <link rel="stylesheet" href="/map/css/MarkerCluster.css" />
    <link rel="stylesheet" href="/map/css/map.css" />
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js" integrity="sha512-bLT0Qm9VnAYZDflyKcBaQ2gg0hSYNQrJ8RilYldYQ1FxQYoCLtUjuuRuZo+fjqhx/qtq/1itJ0C2ejDxltZVFg==" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fomantic-ui/2.8.7/semantic.min.js" integrity="sha512-1Nyd5H4Aad+OyvVfUOkO/jWPCrEvYIsQENdnVXt1+Jjc4NoJw28nyRdrpOCyFH4uvR3JmH/5WmfX1MJk2ZlhgQ==" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/leaflet.js" integrity="sha512-XQoYMqMTK8LvdxXYG3nZ448hOEQiglfqkJs1NOQV44cWnUrBc8PkAOcXy20w0vlaXaVUearIOBhiXZ5V3ynxwA==" crossorigin="anonymous"></script>
    <script src="/map/js/heatmap.js"></script>
    <script src="/map/js/leaflet-heatmap.js"></script>
    <script src="/map/js/leaflet.markercluster.min.js"></script>    
  </head>
  <body>
    <div class="ui placeholder segment" style="background: #fff;">
      <div class="ui icon header">
        <i class="yellow beer icon"></i>
        GABF Winners - <span class="count"></span> <span class="year"></span>
      </div>
      <div class="inline">
        <button class="ui green button" id="play-year">Play History</button>
        <button class="ui orange button" id="map-type-heat">City Heat <i class="fas fa-fire-alt"></i></button>
        <button class="ui blue button" id="map-type-pins" disabled="disabled">Medal Pins <i class="fas fa-medal"></i></button>        
        <button class="ui button" id="reset-year">Reset All</button>
      </div>
      <br/>
      <input id="range-year" type="range" min="1983" max="2020" value="2020" style="width: 100%;" />
      <div style="padding-top: 10px;">
        <label class="first_year tiny ui label" style="float: left; cursor: pointer;">1983</label>
        <label class="current_year tiny ui label" style="float: right; cursor: pointer;"></label>
      </div>
      <a class="ui right corner label" href="https://github.com/avantassel/beerfest-winners-json">
        <i class="red heart icon"></i>
    </a>
    </div>
    
    <div id="map" style="height: 600px; width: 100%;"></div>
    
    <div class="ui message center aligned">
        This heat map shows the winningest cities each year from the Great American Beer Festival.
    </div>
    
    <script>
      const today = new Date();
      const current_year = today.getFullYear();
      var running = false;
      var map_type = 'pin';      
      
      $(document).ready(function(){
        
        const loadJSON = (year, callback) => {
            var json_file = !!year ? '/map/data/'+year+'_by_city.json' : '/map/data/by_city.json';
            if(map_type === 'pin'){
              json_file = !!year ? '/gabf/json/'+year+'.json' : '/map/data/winners.json';
            }
            var xobj = new XMLHttpRequest();
            xobj.overrideMimeType("application/json");
            xobj.open('GET', json_file, true);
            xobj.onreadystatechange = () => {
                if (xobj.readyState === 4 && xobj.status === "200") {
                    callback(xobj.responseText);
                }
            };
            xobj.send(null);
        }

        const baseLayer = L.tileLayer(
          'https://server.arcgisonline.com/ArcGIS/rest/services/World_Topo_Map/MapServer/tile/{z}/{y}/{x}',{
            attribution: '<a href="https://andrewvantassel.com">andrewvantassel.com</a>',
            maxZoom: 18
          }
        );
        
        const cfg = {
          // radius should be small ONLY if scaleRadius is true (or small radius is intended)
          // if scaleRadius is false it will be the constant radius used in pixels
          "radius": 2,
          "maxOpacity": .8,
          // scales the radius based on map zoom
          "scaleRadius": true,
          // if set to false the heatmap uses the global maximum for colorization
          // if activated: uses the data maximum within the current map boundaries
          //   (there will always be a red spot with useLocalExtremas true)
          "useLocalExtrema": true,
          // which field name in your data represents the latitude - default "lat"
          latField: 'lat',
          // which field name in your data represents the longitude - default "lng"
          lngField: 'lng',
          // which field name in your data represents the data value - default "value"
          valueField: 'count'
        };

        const heatmapLayer = new HeatmapOverlay(cfg);
        const markers = L.markerClusterGroup({ chunkedLoading: true, showCoverageOnHover: false, maxClusterRadius: 25 });

        const goldIcon = L.divIcon({
            html: '<i class="fas fa-medal fa-2x"></i>',
            iconSize: [20, 20],
            className: 'gold'
        });
        const silverIcon = L.divIcon({
            html: '<i class="fas fa-medal fa-2x"></i>',
            iconSize: [20, 20],
            className: 'silver'
        });
        const bronzeIcon = L.divIcon({
            html: '<i class="fas fa-medal fa-2x"></i>',
            iconSize: [20, 20],
            className: 'bronze'
        });
        const medalIcon = L.divIcon({
            html: '<i class="fas fa-medal fa-2x"></i>',
            iconSize: [20, 20],
        });
        
        const map = new L.Map('map', {
          center: new L.LatLng(39.9936, -105.0897),
          zoom: 4,
          layers: [baseLayer, heatmapLayer]
        });
        
        function getData(year){
          var json_file = !!year ? '/map/data/'+year+'_by_city.json' : '/map/data/by_city.json';
          if(map_type === 'pin'){
            json_file = !!year ? '/gabf/json/'+year+'.json' : '/map/data/winners.json';
          }
          
          var year_copy = 'Cities in ';
          if(map_type === 'pin')
            year_copy = 'Medals in '
            
          if(year)
            year_copy += year;
          else
            year_copy += 'the last '+(current_year-1983)+' years'
            
          $('.header .year').html(year_copy);
          if(year){
            $('#reset-year').removeClass('disabled');
          } else {
            $('#reset-year').addClass('disabled');
          }
          fetch(json_file)
          .then(response => response.json())
          .then(json => { 
            $('.header .count').html(json.length);
            if(map_type === 'heat'){
              // update layers
              if(map.hasLayer(markers))
                map.removeLayer(markers);
              if(!map.hasLayer(heatmapLayer))
                map.addLayer(heatmapLayer);              
              
              heatmapLayer.setData({max: 1, data: json});              
            } else {
              // update layers
              if(map.hasLayer(heatmapLayer))
                map.removeLayer(heatmapLayer);    
              // clear layers
              markers.clearLayers();
              // build markers
              for(var i = 0; i < json.length; i++){
                if(!json[i].coords) continue;
                var img = getMedal(json[i]);
                var icon = getMedalIcon(json[i]);
                var title = '<b>'+ json[i].brewery + img + '</b>';
                if(!img && json[i].medal)
                  title += '<br/>' + json[i].medal;
                title += '<br/><i>' + json[i].beer + '</i>';
                title += '<br/><b>' + json[i].year;
                if(json[i].style)
                  title += ' ' + json[i].style;
                title += '</b><br/>' + json[i].city + ', '+json[i].state;
                if(icon)
                  var marker = L.marker([json[i].coords[1], json[i].coords[0]], { title: title, icon: icon });
                else
                  var marker = L.marker([json[i].coords[1], json[i].coords[0]], { title: title });
                marker.bindPopup(title);
                markers.addLayer(marker);                  
              }            
              // add markers    
              map.addLayer(markers);                
            }
          });
        }
        
        function getMedal(brewery){          
          if(!brewery.medal) return '';
          if(brewery.medal == 'Gold' || brewery.medal == 'First Place')
            return '<img src="/images/gabf/gold.jpg" class="popup-medal"/>';
          else if(brewery.medal == 'Silver' || brewery.medal == 'Second Place')
            return '<img src="/images/gabf/silver.jpg" class="popup-medal"/>';
          else if(brewery.medal == 'Bronze' || brewery.medal == 'Third Place')
            return '<img src="/images/gabf/bronze.jpg" class="popup-medal"/>';
          return '';
        }
        
        function getMedalIcon(brewery){          
          if(!brewery.medal) return '';
          if(brewery.medal == 'Gold' || brewery.medal == 'First Place')
            return goldIcon;
          else if(brewery.medal == 'Silver' || brewery.medal == 'Second Place')
            return silverIcon;
          else if(brewery.medal == 'Bronze' || brewery.medal == 'Third Place')
            return bronzeIcon;
          return medalIcon;
        }
        
        function StartYears(){
          var year = +$('#range-year').val();
          if(year < current_year && running){
            setTimeout(function(){
              $('#range-year').val(year+1).change();
              StartYears();
            },1000);  
          } else {
            running = false;
            $('#play-year').removeClass('negative').addClass('positive').html('Play History');
            $('#reset-year').prop('disabled', false);
          }
        }
        
        $('.current_year').html(current_year).on('click', function() { 
          $('#range-year').val(current_year).change(); 
        });
        
        $('.first_year').on('click', function() { 
          $('#range-year').val(1983).change(); 
        });
        
        $('#range-year').on('change', function(){
          getData(this.value || null);
        })
        .attr('max', current_year);
        
        $('#reset-year').on('click', function(){
          $('#range-year').val('');
          getData(null);
        });
        
        $('#play-year').on('click', function(){
          if(!running){
            $('#play-year').removeClass('positive').addClass('negative').html('Stop');
            $('#reset-year').prop('disabled','disabled');
            running = true;
            $('#range-year').val(1983).change();
            setTimeout(function(){
              StartYears();
            },1200);
          } else {
            $('#play-year').removeClass('negative').addClass('positive').html('Play History');
            $('#reset-year').prop('disabled', false);
            running = false;
          }
        });
        
        $('#map-type-heat').on('click', function(){
          map_type = 'heat';
          $(this).prop('disabled','disabled');
          $('#map-type-pins').prop('disabled', false);
          getData(null);
        });
        
        $('#map-type-pins').on('click', function(){
          map_type = 'pin';
          $(this).prop('disabled','disabled');
          $('#map-type-heat').prop('disabled', false);
          getData(null);
        });
        
        getData(2020);
      });
    </script>
    <?
    if(file_exists(__DIR__.'/../analytics.php')){
      include __DIR__.'/../analytics.php';
    }
    ?>
  </body>
</html>