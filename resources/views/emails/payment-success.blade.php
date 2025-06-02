<!DOCTYPE html>
<html>
<head>
    <title>Confirmation de paiement</title>
</head>
<body>
    <h1>Confirmation de paiement</h1>
    
    <p>Cher client,</p>
    
    <p>Nous vous confirmons que votre paiement pour la licence {{ $licence->type }} a été effectué avec succès.</p>
    
    <h2>Détails du paiement :</h2>
    <ul>
        <li>Montant : {{ $payment->amount }} {{ $payment->currency }}</li>
        <li>Date : {{ $payment->payment_date }}</li>
        <li>Méthode de paiement : {{ $payment->payment_method }}</li>
    </ul>

    <h2>Code de vérification :</h2>
    <p>Votre code de vérification est : <strong>{{ $verificationCode }}</strong></p>
    <p>Veuillez conserver ce code pour activer votre licence.</p>

    <p>Merci de votre confiance !</p>
</body>
</html> 