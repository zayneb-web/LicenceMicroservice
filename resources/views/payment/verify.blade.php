@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Vérification du paiement</div>

                <div class="card-body">
                    <div class="alert alert-info">
                        Un code de vérification a été envoyé à votre adresse email.
                        Veuillez entrer ce code pour finaliser votre paiement.
                    </div>

                    <form id="verification-form" method="POST" action="{{ route('payment.confirm-verification') }}">
                        @csrf
                        <input type="hidden" name="licence_id" value="{{ $licence->id }}">

                        <div class="form-group">
                            <label for="verification_code">Code de vérification</label>
                            <input type="text" 
                                   class="form-control @error('verification_code') is-invalid @enderror" 
                                   id="verification_code" 
                                   name="verification_code" 
                                   required 
                                   maxlength="6"
                                   pattern="[A-Za-z0-9]{6}"
                                   placeholder="Entrez le code à 6 caractères">
                            @error('verification_code')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group mt-3">
                            <button type="submit" class="btn btn-success">
                                Vérifier le paiement
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('verification-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    try {
        const response = await fetch(this.action, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                licence_id: this.querySelector('[name="licence_id"]').value,
                verification_code: this.querySelector('[name="verification_code"]').value
            })
        });

        const data = await response.json();

        if (data.status === 'success') {
            window.location.href = '{{ route("payment.success") }}';
        } else {
            alert(data.message || 'Une erreur est survenue');
        }
    } catch (error) {
        alert('Une erreur est survenue lors de la vérification');
    }
});
</script>
@endpush
@endsection 