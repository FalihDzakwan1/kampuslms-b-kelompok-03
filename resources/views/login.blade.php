<!DOCTYPE html>
<html>
<head>
    <title>Login KampusLMS</title>
</head>

<body>

<h2>Login KampusLMS</h2>


@if($errors->any())
    <p style="color:red">
        {{ $errors->first() }}
    </p>
@endif


<form method="POST" action="/login">

    @csrf


    <div>
        <label>Email</label>
        <input 
            type="email"
            name="email"
        >
    </div>


    <div>
        <label>Password</label>
        <input 
            type="password"
            name="password"
        >
    </div>


    <button type="submit">
        Login
    </button>


</form>


</body>
</html>