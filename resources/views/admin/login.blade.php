<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Admin Login</title>
    <style>body{font-family:Arial;padding:40px} .box{max-width:360px;margin:0 auto;padding:20px;border:1px solid #eee;border-radius:6px} label{display:block;margin-top:8px} input{width:100%;padding:8px;margin-top:4px} button{margin-top:12px;padding:8px 12px}</style>
</head>
<body>
<div class="box">
    <h2>Admin Login</h2>
    @if($errors->any())<div style="color:red">{{ $errors->first() }}</div>@endif
    <form method="post" action="{{ route('admin.login.post') }}">
        @csrf
        <label>Username<input name="username"/></label>
        <label>Password<input name="password" type="password"/></label>
        <button type="submit">Login</button>
    </form>
</div>
</body>
</html>
