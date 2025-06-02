<!DOCTYPE html>
<html>
<head>
    <title>Paiement Erreur</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="alert alert-danger">
            <h4>Une erreur est survenue lors du paiement.</h4>
            <p>{{ session('error') }}</p>
            <a href="/" class="btn btn-primary">Retour à l'accueil</a>
        </div>
    </div>
</body>
</html>
