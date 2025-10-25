
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-draw/dist/leaflet.draw.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet-draw/dist/leaflet.draw.css" />

<script>
  
    var map = L.map('pikomap').setView([-3.2968, 102.8615], 12);

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors'
  }).addTo(map);
  
  

  // Initialize the draw control
  var drawnItems = new L.FeatureGroup();
    // var editableLayers = new L.FeatureGroup();
  map.addLayer(drawnItems);


 // Custom colors for the drawn shapes
  var polygonOptions = {
    shapeOptions: {
      color: 'blue',
      fillOpacity: 0.4
    }
  };

  var rectangleOptions = {
    shapeOptions: {
      color: 'green',
      fillOpacity: 0.4
    }
  };

  var circleOptions = {
    shapeOptions: {
      color: 'red',
      fillOpacity: 0.4
    }
  };

  var markerOptions = {
    icon: new L.Icon.Default()
  };
  var drawControl = new L.Control.Draw({
    edit: {
      featureGroup: drawnItems
    },
    draw: {
      polygon: polygonOptions,
      rectangle: rectangleOptions,
      circle: circleOptions,
      marker: markerOptions
    }
  });
  map.addControl(drawControl);

//   map.on('draw:created', function (e) {
//     var layer = e.layer;
//     drawnItems.addLayer(layer);

//     // Export drawn shape to GeoJSON
//     var geoJSON = layer.toGeoJSON();
//     console.log(geoJSON);
//     console.log(geoJSON.geometry.coordinates)
//     console.log(geoJSON.geometry.type)
//     $('#tipe').val(geoJSON.geometry.type)
//     $('#latlng').val(geoJSON.geometry.coordinates)
    
//   });

  map.on('draw:created', function(e) {
        var type = e.layerType,
            layer = e.layer;
        
        if(type === 'circle'){
            var obj = layer.getLatLng();
            var radius = layer.getRadius();
            
            $('#tipe').val('circle');
            $('#latlng').val('['+obj.lng+', '+obj.lat+']');
            $('#radius').val(radius);
        }
        else if(type === 'marker'){
            var obj = layer.getLatLng();
            
            $('#tipe').val('marker');
            $('#latlng').val('['+obj.lng+', '+obj.lat+']');
            $('#radius').val(0);
        } else {
            var obj = [];
            layer.getLatLngs()[0].map(function(point) {
                obj.push([point.lng, point.lat]);
            });
            
            $('#tipe').val('polygon');
            $('#latlng').val(JSON.stringify(obj));
            $('#radius').val(0);
        }

        drawnItems.addLayer(layer);
    });

  map.on('draw:edited', function (e) {
        var layers = e.layers;
        
        layers.eachLayer(function (layer) {
            
            if (layer instanceof L.Marker){
                var obj = layer.getLatLng();
            
                $('#tipe').val('marker');
                $('#latlng').val('['+obj.lng+', '+obj.lat+']');
                $('#radius').val(0);
            }

            if (layer instanceof L.Rectangle || layer instanceof L.Polygon) {
                var obj = [];
                layer.getLatLngs()[0].map(function(point) {
                    obj.push([point.lng, point.lat]);
                });
            
                $('#tipe').val('polygon');
                $('#latlng').val(JSON.stringify(obj));
                $('#radius').val(0);
            }

            if (layer instanceof L.Circle) {
                var obj = layer.getLatLng();
                var radius = layer.getRadius();
            
                $('#tipe').val('circle');
                $('#latlng').val('['+obj.lng+', '+obj.lat+']');
                $('#radius').val(radius);
            }

        });
    });
    
    
  function getColor(d) {
    return '#41de21';
           
  }

  function style(feature) {
    return {
      fillColor: getColor(100),
      weight: 2,
      opacity: 1,
      color: 'white',
      dashArray: '3',
      fillOpacity: 0.7
    };
  }

  function onEachFeature(feature, layer) {
    layer.on({
      mouseover: highlightFeature,
      mouseout: resetHighlight
    });
  }

  function highlightFeature(e) {
    var layer = e.target;

    layer.setStyle({
      weight: 5,
      color: '#666',
      dashArray: '',
      fillOpacity: 0.7
    });

    if (!L.Browser.ie && !L.Browser.opera && !L.Browser.edge) {
      layer.bringToFront();
    }
  }

  function resetHighlight(e) {
    geojson.resetStyle(e.target);
  }
  

  // Load the GeoJSON data for US states
  var statesData;
  fetch('<?=base_url()?>kecamatan/getgejson/16.73.04')
    // .then(response => response.json())
    .then(data => {
        console.log(data)
      statesData = data;
      geojson = L.geoJson(statesData, {
        style: style,
        onEachFeature: onEachFeature
      }).addTo(map);
    })
    .catch(error => console.error('Error loading GeoJSON data:', error));
    
</script>