@extends('layout') @section('title', 'Connexion') @section('content')

<hr>

<form>
    <h2 class="form-title">Connexion</h2>
    <hr class="hr-form">
    <div class="card-body container">
        <div class="form-group">
            <label class="form-class-text" for="Email">Adresse Email</label>
            <input type="email" class="form-control" id="Email" aria-describedby="emailHelp" placeholder="Entrer votre adresse mail">
        </div>
        <div class="form-group">
            <label class="form-class-text" for="Password">Mot de passe</label>
            <input type="password" class="form-control" id="Password" placeholder="Mot de passe">
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="text-center">
                    <button type="submit" class="btn btn-white">Connexion</button>
                </div>
            </div>
        </div>
    </div
</form>

<hr class="hr-form">

<div class="form-class-text form-center">
    Pas encore inscrit ?
    <a href="/signup">Inscrivez-vous ici.</a>
</div>
@endsection
