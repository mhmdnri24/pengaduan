<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>

<script>
    var map = L.map('pikomap').setView([0, 0], 2);

    // Set up the OSM layer
    L.tileLayer('<?=base_url('maps/{z}/{x}/{y}.png');?>', {
        attribution: '',
        minZoom: 1,
        maxZoom: 5,
        noWrap: true
    }).addTo(map);

    // Initialise the FeatureGroup to store editable layers
    var editableLayers = new L.FeatureGroup();
    map.addLayer(editableLayers);

    <?php
    $kelurahanlist = $this->kelurahan_m->kelurahan_get_all();
    foreach($kelurahanlist as $row){
        if($row->latlng!=''){
            $color = '#444444';
            $itemName = ($this->uri->segment(3)==$row->id_kelurahan) ? 'itemEdit' : 'item'.$row->id_kelurahan;
            $color = "{color: '$color', fillColor: '$color', fillOpacity: 0.5}";
            if($row->tipe=='circle'){
                echo 'var '.$itemName.' = L.circle('.$row->latlng.', '.$row->radius.', '.$color.').addTo(map).bindPopup("'.$row->nama_kelurahan.'");';
            }
            elseif($row->tipe=='marker'){
                echo 'var '.$itemName.' = L.marker('.$row->latlng.').addTo(map);';
            } else {
                echo 'var '.$itemName.' = L.polygon('.$row->latlng.', '.$color.').addTo(map).bindPopup("'.$row->nama_kelurahan.'");';
            }
            echo "\n";
            if($this->uri->segment(2)=='edit' && $this->uri->segment(3)==$row->id_kelurahan)
                echo $itemName.'.editing.enable();';
        }
    }
    ?>

    <?php if(isset($detailkel) && $detailkel->latlng!=''):?>
        let btn = document.createElement('button');
            btn.setAttribute("type", "button");
            btn.setAttribute("class", "btn btn-danger");
            btn.innerText = 'Hapus Marker';
            btn.onclick =  function() {
                map.removeLayer(itemEdit);
            }

        itemEdit.bindPopup(btn, {maxWidth: 'auto'});
    <?php endif;?>

    var drawPluginOptions = {
        position: 'topright',
        draw: {
            polyline: false,
            polygon: true,
            circle: true,
            rectangle: true,
            marker: true,
            circlemarker: false
            },
        edit: {
            featureGroup: editableLayers,
            remove: true
        }
    };

    // Initialise the draw control and pass it the FeatureGroup of editable layers
    var drawControl = new L.Control.Draw(drawPluginOptions);
    map.addControl(drawControl);

    map.on('draw:created', function(e) {
        var type = e.layerType,
            layer = e.layer;
        
        if(type === 'circle'){
            var obj = layer.getLatLng();
            var radius = layer.getRadius();
            
            $('#tipe').val('circle');
            $('#latlng').val('['+obj.lat+', '+obj.lng+']');
            $('#radius').val(radius);
        }
        else if(type === 'marker'){
            var obj = layer.getLatLng();
            
            $('#tipe').val('marker');
            $('#latlng').val('['+obj.lat+', '+obj.lng+']');
            $('#radius').val(0);
        } else {
            var obj = [];
            layer.getLatLngs()[0].map(function(point) {
                obj.push([point.lat, point.lng]);
            });
            
            $('#tipe').val('polygon');
            $('#latlng').val(JSON.stringify(obj));
            $('#radius').val(0);
        }

        editableLayers.addLayer(layer);
    });
    
    map.on('draw:edited', function (e) {
        var layers = e.layers;
        
        layers.eachLayer(function (layer) {
            
            if (layer instanceof L.Marker){
                var obj = layer.getLatLng();
            
                $('#tipe').val('marker');
                $('#latlng').val('['+obj.lat+', '+obj.lng+']');
                $('#radius').val(0);
            }

            if (layer instanceof L.Rectangle || layer instanceof L.Polygon) {
                var obj = [];
                layer.getLatLngs()[0].map(function(point) {
                    obj.push([point.lat, point.lng]);
                });
            
                $('#tipe').val('polygon');
                $('#latlng').val(JSON.stringify(obj));
                $('#radius').val(0);
            }

            if (layer instanceof L.Circle) {
                var obj = layer.getLatLng();
                var radius = layer.getRadius();
            
                $('#tipe').val('circle');
                $('#latlng').val('['+obj.lat+', '+obj.lng+']');
                $('#radius').val(radius);
            }

        });
    });


    <?php if(isset($detailkel) && $detailkel->latlng!=''):?>
    <?php if($this->uri->segment(2)=='edit'):
    $kelurahan = $this->kelurahan_m->kelurahan_by_id($this->uri->segment(3));?>
    itemEdit.on('edit', function() {
        var type = '<?=$kelurahan->tipe;?>';

        if(type === 'circle'){
            var obj = this.getLatLng();
            var radius = this.getRadius();
            
            $('#tipe').val('circle');
            $('#latlng').val('['+obj.lat+', '+obj.lng+']');
            $('#radius').val(radius);
        }
        else if(type === 'marker'){
            var obj = this.getLatLng();
            
            $('#tipe').val('marker');
            $('#latlng').val('['+obj.lat+', '+obj.lng+']');
            $('#radius').val(0);
        } else {
            var obj = [];
            this.getLatLngs()[0].map(function(point) {
                obj.push([point.lat, point.lng]);
            });
            
            $('#tipe').val('polygon');
            $('#latlng').val(JSON.stringify(obj));
            $('#radius').val(0);
        }
	});
    <?php endif;?>
    <?php endif;?>

    function clearPoint(){
        L.marker().itemEdit.remove();
    }
    </script>