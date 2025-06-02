<!DOCTYPE html>
<html>
<head>
    <title>Paiement Réussi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h3 class="mb-0">Paiement Réussi</h3>
                    </div>
                    <div class="card-body">
                        <h4>Merci pour votre achat !</h4>
                        <p>Votre licence a été activée avec succès.</p>
                        
                        <div class="mt-4">
                            <h5>Détails de la licence :</h5>
                            <ul class="list-unstyled">
                                <li><strong>Type :</strong> {{ $licence->type }}</li>
                                <li><strong>Prix :</strong> {{ $licence->price }} EUR</li>
                                <li><strong>Date d'activation :</strong> {{ $licence->activated_at->format('d/m/Y H:i') }}</li>
                            </ul>
                        </div>

                        <div class="mt-4">
                            <a href="/" class="btn btn-primary">Retour à l'accueil</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 