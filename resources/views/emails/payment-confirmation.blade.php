<!DOCTYPE html>
<html>
<head>
    <title>Confirmation de paiement</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .content {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 5px;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 12px;
            color: #666;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Confirmation de paiement</h1>
        </div>
        
        <div class="content">
            <p>Bonjour,</p>
            
            <p>Nous confirmons la réception de votre paiement pour votre licence.</p>
            
            <h3>Détails de la licence :</h3>
            <ul>
                <li>Type de licence : {{ $licence->type }}</li>
                <li>Prix payé : {{ $payment->amount }} {{ $payment->currency }}</li>
                <li>Durée : {{ $licence->duration_months }} mois</li>
                <li>Date de paiement : {{ $payment->payment_date->format('d/m/Y H:i') }}</li>
                <li>Méthode de paiement : {{ $payment->payment_method }}</li>
            </ul>

            <a href="{{ url('/licences') }}" class="button">Voir les détails de votre licence</a>
            
            <p>Merci de votre confiance !</p>
        </div>
        
        <div class="footer">
            <p>Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
        </div>
    </div>
</body>
</html> 