@extends('layout') @section('title', 'Inscription') @section('content')
<div class "container">
    <hr>

    <form>
        <h2 class="form-title">Inscription</h2>
        <hr class="hr-form">
        <div class="card-body container card-space">
            <div class "card">
                <div class="form-group">
                    <label class="form-class-text" for="Email">Adresse Email</label>
                    <input type="email" class="form-control" id="Email" aria-describedby="emailHelp" placeholder="Entrer votre adresse mail">
                </div>
                <div class="form-group">
                    <label class="form-class-text" for="Pseudo">Pseudo</label>
                    <input type="email" class="form-control" id="Pseudo" aria-describedby="emailHelp" placeholder="Entrer votre pseudo">
                </div>
                <div class="form-group">
                    <label class="form-class-text" for="Password">Mot de passe</label>
                    <input type="password" class="form-control" id="Password" placeholder="Mot de passe">
                </div>
                <div class="form-group">
                    <label class="form-class-text" for="ConfPassword">Vérification de Mot de passe</label>
                    <input type="password" class="form-control" id="ConfPassword" placeholder="Veuillez ré-entrer votre mot de passe">
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <div class="text-center">
                            <button type="submit" class="btn btn-white btn-padding">Inscription</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <hr class="hr-form">

    <div class="form-class-text form-center">Vous avez déjà un compte ?
        <a href="/connect">Connectez-vous ici.</a>
    </div>
</div>
@endsection