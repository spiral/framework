<!DOCTYPE html>
<html>
<head>
    <title>${title}</title>
    <style>
        html {
            height: 100%;
        }

        body {
            height: 100%;
            min-height: 100%;
            margin: 0;
            color: #595a59;
            font-family: "Helvetica", sans-serif;
            font-weight: lighter;
            font-size: 14px;
        }

        .wrapper {
            position: relative;
            top: 50%;
            transform: translateY(-50%);
            -webkit-transform: translateY(-50%);
            text-align: center;
        }

        .code {
            font-size: 80px;
            line-height: 60px;
            margin-bottom: 40px;
            font-weight: bold;
        }

        a {
            color: #5fa4ea;
        }

        .title {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="placeholder">
        <div class="code">${code}</div>
        <div class="title">${title}</div>
        <div class="message">${message}</div>
    </div>
</div>
</body>
</html>