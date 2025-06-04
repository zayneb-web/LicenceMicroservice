@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Paiement réussi</div>

                <div class="card-body">
                    <div class="alert alert-success">
                        <h4 class="alert-heading">Félicitations !</h4>
                        <p>Votre paiement a été traité avec succès et votre licence est maintenant active.</p>
                    </div>

                    <div class="text-center mt-4">
                        <a href="{{ url('/') }}" class="btn btn-primary">
                            Retour à l'accueil
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection