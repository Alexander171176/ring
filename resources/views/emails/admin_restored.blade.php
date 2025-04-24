<!DOCTYPE html>
<html lang="ru">
<head><meta charset="UTF-8"><title>Восстановление админа</title></head>
<body>
<h2>Системная учётная запись была восстановлена</h2>
<p>Имя: <strong>{{ $user->name }}</strong></p>
<p>Email: <strong>{{ $user->email }}</strong></p>
<p>Дата восстановления: <strong>{{ now()->toDateTimeString() }}</strong></p>
</body>
</html>
