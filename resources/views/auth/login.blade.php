<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Homeu</title>

    <style>
      @use postcss-preset-env;

      /* helpers/align.css */

      .align {
        align-items: center;
        display: flex;
        justify-content: center;
      }

      /* helpers/grid.css */

      :root {
        --gridMaxWidth: 40em;
        --gridWidth: 90%;
      }

      .grid {
        margin-left: auto;
        margin-right: auto;
        max-width: var(--gridMaxWidth);
        width: var(--gridWidth);
      }

      /* helpers/icon.css */

      .icon {
        display: inline-block;
        height: 1.25em;
        line-height: 1.25em;
        margin-right: 0.625em;
        text-align: center;
        vertical-align: middle;
        width: 1.25em;
      }

      .icon--info {
        background-color: #e5e5e5;
        border-radius: 50%;
      }

      /* layout/base.css */

      :root {
        --bodyBackgroundColor: #eaeaea;
        --bodyColor: #999;
        --bodyFontFamily: 'Helvetica', 'Arial';
        --bodyFontFamilyFallback: sans-serif;
        --bodyFontSize: 0.875rem;
        --bodyFontWeight: 400;
        --bodyLineHeight: 1.5;
      }

      *,
      *::before,
      *::after {
        box-sizing: inherit;
      }

      html {
        box-sizing: border-box;
        height: 100%;
      }

      body {
        background-color: var(--bodyBackgroundColor);
        font-family: var(--bodyFontFamily), var(--bodyFontFamilyFallback);
        font-size: var(--bodyFontSize);
        font-weight: var(--bodyFontWeight);
        line-height: var(--bodyLineHeight);
        margin: 0;
        min-height: 100%;
      }

      /* modules/anchor.css */

      :root {
        --anchorColor: inherit;
        --anchorHoverColor: #1dabb8;
      }

      a {
        color: var(--anchorColor);
        text-decoration: none;
        transition: color 0.3s;
      }

      a:hover {
        color: var(--anchorHoverColor);
      }

      /* modules/form.css */

      fieldset {
        border: none;
        margin: 0;
      }

      input {
        appearance: none;
        border: none;
        font: inherit;
        margin: 0;
        outline: none;
        padding: 0;
        font-size: 18px;
      }

      input[type='submit'] {
        cursor: pointer;
      }

      .form input[type='email'],
      .form input[type='password'] {
        width: 100%;
      }

      /* modules/login.css */

      :root {
        --loginBorderRadius: 0.25em;
        --loginHeaderBackgroundColor: #282830;

        --loginInputBorderRadius: 0.25em;
      }

      .login__header {
        background-color: #689BCA;
        border-top-left-radius: var(--loginBorderRadius);
        border-top-right-radius: var(--loginBorderRadius);
        color: #fff;
        padding: 1.5em;
        text-align: center;
        text-transform: uppercase;
      }

      .login__title {
        font-size: 30px;
        margin: 0;
      }

      .login__body {
        background-color: #fff;
        padding: 50px 1.5em;
        position: relative;
      }

      .login__body::before {
        background-color: #fff;
        content: '';
        height: 0.5em;
        left: 50%;
        margin-left: -0.25em;
        margin-top: -0.25em;
        position: absolute;
        top: 0;
        transform: rotate(45deg);
        width: 0.5em;
      }

      .login input[type='email'],
      .login input[type='password'] {
        border: 0.0625em solid #e5e5e5;
        padding: 1em 1.25em;
      }

      .login input[type='email'] {
        border-top-left-radius: var(--loginInputBorderRadius);
        border-top-right-radius: var(--loginInputBorderRadius);
      }

      .login input[type='password'] {
        border-bottom-left-radius: var(--loginInputBorderRadius);
        border-bottom-right-radius: var(--loginInputBorderRadius);
        border-top: 0;
      }

      .login input[type='submit'] {
        background-color: #1dabb8;
        border-radius: var(--loginInputBorderRadius);
        color: #fff;
        font-weight: 700;
        order: 1;
        padding: 0.75em 1.25em;
        transition: background-color 0.3s;
      }

      .login input[type='submit']:focus,
      .login input[type='submit']:hover {
        background-color: #198d98;
      }

      .login__footer {
        align-items: center;
        background-color: #fff;
        border-bottom-left-radius: var(--loginBorderRadius);
        border-bottom-right-radius: var(--loginBorderRadius);
        display: flex;
        justify-content: space-between;
        padding-bottom: 1.5em;
        padding-left: 1.5em;
        padding-right: 1.5em;
      }

      .login__footer p {
        margin: 0;
      }

      .is-invalid { 
        border-color: #dc3545 !important;
        padding-right: calc(1.5em + .75rem);
        background-image: url(data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='%23dc3545' viewBox='-2 -2 7 7'%3e%3cpath stroke='%23dc3545' d='M0 0l3 3m0-3L0 3'/%3e%3ccircle r='.5'/%3e%3ccircle cx='3' r='.5'/%3e%3ccircle cy='3' r='.5'/%3e%3ccircle cx='3' cy='3' r='.5'/%3e%3c/svg%3E);
        background-repeat: no-repeat;
        background-position: center right calc(.375em + .1875rem);
        background-size: calc(.75em + .375rem) calc(.75em + .375rem); 
      }

      .invalid-feedback { 
        display: none;
        width: 100%;
        margin-top: .25rem;
        font-size: 80%;
        color: #dc3545;
      }

      .is-invalid~.invalid-feedback { display: block; }

    </style>
  </head>

  <body class="align">
    <div class="grid">
      <form method="POST" action="{{ route('login') }}" class="form login">
        @csrf
        <header class="login__header">
          <h3 class="login__title">Login</h3>
        </header>

        <div class="login__body">
          <div class="form__field">
            <input type="email" placeholder="Email" class="{{ $errors->has('email') ? 'is-invalid' : '' }}" name="email" required>
            @if ($errors->has('email'))
              <span class="invalid-feedback" role="alert">
                <strong>{{ $errors->first('email') }}</strong>
              </span>
            @endif
          </div>

          <div class="form__field">
            <input type="password" placeholder="Password" class="{{ $errors->has('password') ? 'is-invalid' : '' }}" name="password" required>
            @if ($errors->has('password'))
              <span class="invalid-feedback" role="alert">
                <strong>{{ $errors->first('password') }}</strong>
              </span>
            @endif
          </div>
        </div>

        <footer class="login__footer">
          <input type="submit" value="Login">

          <!-- <p><span class="icon icon--info">?</span><a href="#">Forgot Password</a></p> -->
        </footer>
      </form>
    </div>
  </body>
</html>
