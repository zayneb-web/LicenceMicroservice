<!DOCTYPE html>
<html>
    <head>
        <title>Buy a Licence</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://js.stripe.com/v3/"></script>
    </head>
    <body>
        <div class="container mt-5">
            <div class="row justify-content-center">
                @foreach($licences as $licence)
                    <div class="col-md-4">
                        <div class="card mb-4">
                            <div class="card-header text-center">
                                <h4>{{ ucfirst($licence->type) }}</h4>
                            </div>
                            <div class="card-body text-center">
                                <h2>{{ $licence->price }} €</h2>
                                <form action="{{ route('checkout') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="licence_id" value="{{ $licence->id }}">
                                    <button type="submit" class="btn btn-primary">Acheter</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </body>
</html>