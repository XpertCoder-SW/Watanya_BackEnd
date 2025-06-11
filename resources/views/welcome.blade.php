<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    

<form method="post" action="/api/setting">
    @csrf
    <input type="number" name="showGrades" value="true">
    <input type="text" name="academic_year" value="2023-2024">
    <input type="text" name="current_semester" value="One">


    <input type="submit" value="submit">
</form>

</body>
</html>