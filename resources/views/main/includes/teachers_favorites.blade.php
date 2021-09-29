<canvas id="teachers_favorites"></canvas>

<script>
    $(document).ready(function () {

        ctx = document.getElementById('teachers_favorites').getContext('2d');

        data = {
            datasets: [{
                data: [@foreach($teachers as $favs){{$favs}},@endforeach],
                backgroundColor: "#0091ea",
                label: "Selected as favorite"
            }],

            // These labels appear in the legend and in the tooltips when hovering different arcs
            labels: [@foreach($teachers as $k=>$favs)'{{$k}}',@endforeach]
        };

        myPieChart = new Chart(ctx,{
            type: 'bar',
            data: data,
            options: {}
        });

    });
</script>