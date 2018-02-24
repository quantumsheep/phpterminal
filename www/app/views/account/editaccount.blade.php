@extends('layout') @section('title', 'Gestion de compte') @section('content')

<hr>

<form>
    <h2 class="form-title">Changement de mot de passe</h2>
    <hr class="hr-form">
    <div class="card-body container">
        <div class="form-group">
            <label class="form-class-text" for="Email">Mot de passe</label>
            <input type="email" class="form-control" id="Email" aria-describedby="emailHelp" placeholder="Entrer votre mot de passe actuel">
        </div>
        <div class="form-group">
            <label class="form-class-text" for="Password">Nouveau mot de passe</label>
            <input type="password" class="form-control" id="Password" placeholder="Entrer votre nouveau mot de passe">
        </div>
        <div class="form-group">
            <label class="form-class-text" for="Password">Vérification de mot de passe</label>
            <input type="password" class="form-control" id="Password" placeholder="Re-entrer votre nouveau mot de passe">
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="text-center">
                    <button type="submit" class="btn btn-white">Changer de mot de passe</button>
                </div>
            </div>
        </div>
    </div>
</form>

<hr class="hr-form">

<form>
    <h2 class="form-title">Modification d'adresse email</h2>
    <hr class="hr-form">
    <div class="card-body container">
        <div class="form-group">
            <label class="form-class-text" for="Email">Adresse Email</label>
            <input type="email" class="form-control" id="Email" aria-describedby="emailHelp" placeholder="Entrer votre adresse mail actuelle">
        </div>
        <div class="form-group">
            <label class="form-class-text" for="Password">Nouvelle adresse email</label>
            <input type="password" class="form-control" id="Password" placeholder="Entrer votre nouvelle adresse email">
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="text-center">
                    <button type="submit" class="btn btn-white btn-padding">Changer d'adresse email</button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection