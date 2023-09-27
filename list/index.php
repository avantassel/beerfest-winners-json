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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fomantic-ui/2.8.7/semantic.min.css" integrity="sha512-g/MzOGVPy3OQ4ej1U+qe4D/xhLwUn5l5xL0Fa7gdC258ZWVJQGwsbIR47SWMpRxSPjD0tfu/xkilTy+Lhrl3xg==" crossorigin="anonymous" />
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js" integrity="sha512-bLT0Qm9VnAYZDflyKcBaQ2gg0hSYNQrJ8RilYldYQ1FxQYoCLtUjuuRuZo+fjqhx/qtq/1itJ0C2ejDxltZVFg==" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fomantic-ui/2.8.7/semantic.min.js" integrity="sha512-1Nyd5H4Aad+OyvVfUOkO/jWPCrEvYIsQENdnVXt1+Jjc4NoJw28nyRdrpOCyFH4uvR3JmH/5WmfX1MJk2ZlhgQ==" crossorigin="anonymous"></script>
    <script src="js/tablesort.js"></script>
    <style>
    .gold {
      color: #fbbd08;
    }
    .silver {
      color: #767676;
    }
    .bronze {
      color: #a5673f;
    }
    .ui.selection.dropdown {
      padding: 5px;
    }
    </style> 
  </head>
  <body>
    
  <div style="text-align: center; padding: 20px;">
  
  <h1 class="ui header centered">Beer Competition Winners</h1>
  
  <select id="comp" class="ui selection dropdown" onchange="changeCompetition();">
    <option value="gabf" selected>Great American Beer Fest</option>
    <option value="wbc">World Beer Cup</option>
    <option value="usopen">US Open Beer Championship</option>
  </select>
  <select id="year" class="ui selection dropdown" onchange="loadWinners();">
  </select>
  <div class="ui input">
    <input id="search" type="text" placeholder="Search..." onkeypress="searchList(this);">
  </div>

  <div class="ui statistics" style="align-self: center; display: revert;">
  <div id="stat-total" class="statistic" style="cursor: pointer;" onclick="$('#search').val('').keypress();">
    <div class="value">
    </div>
    <div class="label">
      Medals
    </div>
  </div>
  <div id="stat-gold" class="statistic" style="cursor: pointer;" onclick="$('#search').val('Gold').keypress();">
    <div class="value">
      <i class="medal gold icon"></i> <span></span>
    </div>
    <div class="label">
      Gold
    </div>
  </div>
  <div id="stat-silver" class="statistic" style="cursor: pointer;" onclick="$('#search').val('Silver').keypress();">
    <div class="value">
      <i class="medal silver icon"></i> <span></span>
    </div>
    <div class="label">
      Silver
    </div>
  </div>
  <div id="stat-bronze" class="statistic" style="cursor: pointer;" onclick="$('#search').val('Bronze').keypress();">
    <div class="value">
      <i class="medal bronze icon"></i> <span></span>
    </div>
    <div class="label">
      Bronze
    </div>
  </div>
</div>
</div>
    
  <div style="text-align: center;">
    <a href="../map">Check out the map</a>
  </div>
    
  <table class="ui celled sortable striped table">
  <thead>
  <tr>
    <th>Brewery</th>
    <th>City</th>
    <th>State</th>
    <th>Beer</th>
    <th>Medal</th>
    <th>Category</th>
  </tr>
  </thead>
  
  <tbody id="tableBody">
  
  </tbody>  
  
  </table>
    
  <script>
    $(document).ready(function(){
      $('table').tablesort();
      $('#comp').change();
      $('#year').change();
    });
    
    function searchList(e){
      var search = $(e).val();
      $('#tableBody tr').show();
      if(search){
        $('#tableBody tr').map(function(){
          if($(this).html().indexOf(search) === -1)
            $(this).hide();
          else
            $(this).show();
        });
      }
    }
    
    function changeCompetition(){
      var currentYear = new Date().getFullYear();
      var startYear = currentYear;
      var even = false;
      switch($('#comp').val()){
        case 'gabf':
          startYear =  1983;
        break;
        case 'wbc':
          startYear =  1996;
          even = true;
        break;
        case 'usopen':
          startYear =  2009;
        break;
      }
      var options = [];
      for(var i = startYear; i <= currentYear; i++){
        if(i == currentYear)
          options.push('<option selected>'+i+'</option>');
        else
          options.push('<option>'+i+'</option>');
        if(even) i++;
      }
      $('#year').empty().append(options.join(''));
      $('#year').change();
    }
    
    function loadWinners(){
      var comp = $('#comp').val();
      var year = $('#year').val();
      var json_file = '/'+comp+'/json/'+year+'.json';
      $('#tableBody').empty();
      var winners = [];
      var html = '';
      fetch(json_file)
          .then(response => response.json())
          .then(json => { 
            var gold = 0;
            var silver = 0;
            var bronze = 0;
            $('#stat-total .value').html(json.length);
            for(var i = 0; i < json.length; i++){
              var img = getMedal(json[i]);
              if(img.indexOf('gold') !== -1)
                gold++;
              if(img.indexOf('silver') !== -1)
                silver++;
              if(img.indexOf('bronze') !== -1)
                bronze++;
              html = '<tr>';
              html += '<td>'+json[i]['brewery']+'</td>';
              html += '<td>'+(json[i]['city'] || '')+'</td>';
              html += '<td>'+(json[i]['state'] || '')+'</td>';
              html += '<td>'+json[i]['beer']+'</td>';
              html += '<td>'+img+' '+json[i]['medal']+'</td>';
              html += '<td>'+json[i]['style']+'</td>';
              html += '<\tr>';
              winners.push(html);
            }
            $('#tableBody').html(winners.join(''));
            $('#stat-gold .value span').html(gold);
            $('#stat-silver .value span').html(silver);
            $('#stat-bronze .value span').html(bronze);
          });
    }
    
    function getMedal(brewery){          
      if(!brewery.medal) return '';
      if(brewery.medal == 'Gold' || brewery.medal == 'First Place')
        return '<i class="fas fa-medal gold fa-lg"></i>';
      else if(brewery.medal == 'Silver' || brewery.medal == 'Second Place')
        return '<i class="fas fa-medal silver fa-lg"></i>';
      else if(brewery.medal == 'Bronze' || brewery.medal == 'Third Place')
        return '<i class="fas fa-medal bronze fa-lg"></i>';
      return '';
    }
  </script>
  </body>
  </html>