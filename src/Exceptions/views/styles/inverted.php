<style>
    body.spiral-exception {
        font-family: Helvetica, sans-serif;
        background-color: #e0e0e0;
        font-size: 14px;
        padding: 5px;
        color: rgba(71, 86, 105, .95);
    }

    .spiral-exception .wrapper {
        padding: 5px;
        background-color: #ddd;
    }

    .spiral-exception .wrapper strong {
        font-weight: bold;
    }

    .spiral-exception .wrapper i {
        font-style: italic;
    }

    .spiral-exception .dump {
        padding: 5px;
        background-color: white;
        margin-top: 0;
        display: none;
        overflow-x: auto;
    }

    .spiral-exception .wrapper .header {
        margin-bottom: 5px;
        background-color: #990000;
        border: 2px solid #990000;
        padding: 8px 13px 8px 13px;
        color: #fff;
    }

    .spiral-exception .wrapper .header .previous {
        font-size: 10px;
        opacity: 0.6;

        margin-top: 4px;
    }

    .spiral-exception .wrapper .header .previous:hover {
        opacity: 1;
    }

    .spiral-exception .wrapper .query {
        margin-bottom: 5px;
        background-color: #ffeaaa;
        border: 2px solid #ffeaaa;
        padding: 8px 13px 8px 18px;
        color: black;
        white-space: pre;
    }

    .spiral-exception .wrapper .stacktrace {
        display: inline-block;
        width: 100%;
    }

    .spiral-exception .wrapper .stacktrace .trace {
        font-family: Monospace;
        float: left;
        width: 65%;
    }

    .spiral-exception .wrapper .stacktrace .trace .container {
        padding: 15px;
        background-color: white;
        margin-bottom: 5px;
        overflow-x: auto;
    }

    .spiral-exception .wrapper .stacktrace .trace .container.no-trace {
        color: black;
    }

    .spiral-exception .wrapper .stacktrace .trace .container.no-trace .arguments span {
        cursor: pointer;
    }

    .spiral-exception .wrapper .stacktrace .trace .container.no-trace .arguments span:hover {
        text-decoration: underline;
    }

    .spiral-exception .wrapper .stacktrace .trace .location {
        color: black;
        margin-bottom: 5px;
    }

    .spiral-exception .wrapper .stacktrace .trace .location .arguments span:hover {
        text-decoration: underline;
        cursor: pointer;
    }

    .spiral-exception .wrapper .stacktrace .trace .location em {
        color: #636363;
        font-style: normal;
    }

    .spiral-exception .wrapper .stacktrace .trace .lines div {
        white-space: pre;
    }

    .spiral-exception .wrapper .stacktrace .trace .lines div .number {
        display: inline-block;
        width: 50px;
        color: #757575;
    }

    .spiral-exception .wrapper .stacktrace .trace .lines div:hover {
        background-color: #f2f1f1;
    }

    .spiral-exception .wrapper .stacktrace .trace .lines div.active {
        background-color: #fff8b5;
    }

    .spiral-exception .wrapper .stacktrace .chain {
        width: 35%;
        float: right;
    }

    .spiral-exception .wrapper .stacktrace .chain .calls {
        padding: 10px 10px 10px 10px;
        margin-left: 5px;
        background-color: white;
        margin-bottom: 5px;
        overflow-x: auto;
    }

    .spiral-exception .wrapper .stacktrace .chain .call .function {
        font-size: 11px;
        color: black;
    }

    .spiral-exception .wrapper .stacktrace .chain .call .function .arguments span {
        cursor: pointer;
    }

    .spiral-exception .wrapper .stacktrace .chain .call .function .arguments span:hover {
        text-decoration: underline;
    }

    .spiral-exception .wrapper .stacktrace .chain .call .location {
        margin-bottom: 10px;
        font-size: 10px;
        color: #636363;
    }

    .spiral-exception .wrapper .stacktrace .chain .dumper {
        padding-left: 5px;
        padding-bottom: 5px;
        display: none;
    }

    .spiral-exception .wrapper .stacktrace .chain .dumper .close {
        text-align: right;
        padding: 2px;
        color: #151515;
        cursor: pointer;
        font-size: 12px;
        background-color: white;
    }

    .spiral-exception .wrapper .stacktrace .chain .dumper .close:hover {
        background-color: #e5e5e5;
    }

    .spiral-exception .wrapper .variables .container {
        margin-bottom: 9px;
    }

    .spiral-exception .wrapper .variables .container table tr {
        background-color: white;
    }

    .spiral-exception .wrapper .variables .container table tr:nth-child(odd) {
        background-color: #f6f6f6;
    }

    .spiral-exception .wrapper .variables .container table tr:hover {
        background-color: #e5e5e5;
    }

    .spiral-exception .wrapper .variables .container table td {
        padding: 3px;
    }

    .spiral-exception .wrapper .variables .container table td.name {
        font-weight: bold;
        vertical-align: top;
    }

    .spiral-exception .wrapper .variables .title, .spiral-exception .wrapper .messages .title {
        padding: 7px 7px 7px 5px;
        background-color: #e7c35e;
        font-weight: bold;
        color: #444;
        cursor: pointer;
    }

    .spiral-exception .wrapper .footer {
        margin-top: 10px;
        margin-bottom: 5px;
        font-size: 12px;
    }

    .spiral-exception .wrapper .footer .date {
        color: #1d1d1d;
    }

    .spiral-exception .wrapper div.messages {
        margin-bottom: 10px;
    }

    .spiral-exception .wrapper div.messages div.title {
        padding: 7px;
        padding-left: 5px;
        background-color: #669933;
        font-weight: bold;
        color: white;
    }

    .spiral-exception .wrapper .messages .container table {
        width: 100%;
    }

    .spiral-exception .wrapper .messages .container table tr {
        background-color: white;
    }

    .spiral-exception .wrapper .messages .container table tr:nth-child(odd) {
        background-color: #f8f8f8;
    }

    .spiral-exception .wrapper .messages .container table tr:hover {
        background-color: #e5e5e5;
    }

    .spiral-exception .wrapper .messages .container table td {
        padding: 3px;
    }

    .spiral-exception .wrapper .messages .container table td.channel {
        font-weight: bold;
        vertical-align: top;
    }

    .spiral-exception .wrapper .messages .container table td.message {
        width: 100%;
        font-family: monospace;
        white-space: pre;
    }

    .spiral-exception .wrapper .tags .tag {
        font-size: 15px;
        font-family: monospace;
        margin: 0px 10px 10px 0px;
        background-color: white;
        display: inline-block;
    }

    .spiral-exception .wrapper .tags .tag:hover {
        background-color: #e5e5e5;
    }

    .spiral-exception .wrapper .tags .tag .name {
        font-weight: bold;
        display: inline-block;
        padding: 7px;
        background-color: #5fa4ea;
        color: white;
    }

    .spiral-exception .wrapper .tags .tag .value {
        padding: 7px;
        display: inline-block;
    }
</style>
