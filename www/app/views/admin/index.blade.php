@extends('admin/layout')
@section('title', 'Administration')

@section('content')
    <section class="page-content-wrapper">
        <div class="container-fluid">
            <h1>Dashboard</h1>
            <p>Welcome to alph Terminal dashboard</p>
            <section>
                <div class="col-md-6">
                    <canvas id="accountCreationChart"></canvas>
                </div>
                <div class="col-md-6">
                    <canvas id="terminalCreationChart"></canvas>
                </div>
            </section>
        </div>
    </section>
@endsection

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.2/Chart.js" integrity="sha256-J2sc79NPV/osLcIpzL3K8uJyAD7T5gaEFKlLDM18oxY=" crossorigin="anonymous"></script>
<script>
const ctx = document.getElementById('accountCreationChart').getContext('2d');
const chart = new Chart(ctx, {
    // The type of chart we want to create
    type: 'line',

    // The data for our dataset
    data: {
        labels: [{!! $model->accountCreatedDataDates !!}],
        datasets: [{
            label: "Accounts created from the past month",
            backgroundColor: 'rgb(73, 183, 255)',
            borderColor: 'rgb(73, 183, 255)',
            data: [{{ $model->accountCreatedDataNumbers }}],
        }]
    },

    // Configuration options go here
    options: {}
});
</script>
@endsection
