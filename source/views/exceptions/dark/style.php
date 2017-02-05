<style>
    body.spiral-exception {
        font-family: Helvetica, sans-serif;
        background-color: #25000a;
        font-size: 14px;
        padding: 5px;
        color: #a1a1a1;
    }

    .spiral-exception .wrapper {
        padding: 5px;
        box-shadow: 0 0 10px rgba(0, 0, 0, .7);
        background-color: #3a3a3a;
    }

    .spiral-exception .wrapper strong {
        font-weight: bold;
    }

    .spiral-exception .wrapper i {
        font-style: italic;
    }

    .spiral-exception .dump {
        padding: 5px;
        background-color: #232323;
        margin-top: 0;
        display: none;
        overflow-x: auto;
    }

    .spiral-exception .wrapper .header {
        margin-bottom: 5px;
        background: #d3833b;
        border: 2px solid #d3833b;
        padding: 8px 13px 8px 18px;
        color: #fff;
        box-shadow: inset 0 0 8px rgba(0, 0, 0, .2);
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
        background: rgb(251, 217, 139);
        border: 2px solid rgb(251, 217, 139);
        padding: 8px 13px 8px 18px;
        color: black;
        box-shadow: inset 0 0 8px rgba(0, 0, 0, .2);
        white-space: pre;
    }

    .spiral-exception .wrapper .stacktrace {
        display: inline-block;
        width: 100%;
    }

    .spiral-exception .wrapper .stacktrace .trace {
        font-family: Monospace;
        float: left;
        width: 60%;
    }

    .spiral-exception .wrapper .stacktrace .trace .container {
        padding: 15px;
        background-color: #2e2e2e;
        margin-bottom: 5px;
        overflow-x: auto;
        box-shadow: inset 0 0 20px rgba(0, 0, 0, .2);
    }

    .spiral-exception .wrapper .stacktrace .trace .container.no-trace {
        color: #6bbdff;
    }

    .spiral-exception .wrapper .stacktrace .trace .container.no-trace .arguments span {
        cursor: pointer;
    }

    .spiral-exception .wrapper .stacktrace .trace .container.no-trace .arguments span:hover {
        text-decoration: underline;
    }

    .spiral-exception .wrapper .stacktrace .trace .location {
        color: #6bbdff;
        margin-bottom: 5px;
    }

    .spiral-exception .wrapper .stacktrace .trace .location .arguments span:hover {
        text-decoration: underline;
        cursor: pointer;
    }

    .spiral-exception .wrapper .stacktrace .trace .location em {
        color: #a1a1a1;
        font-style: normal;
    }

    .spiral-exception .wrapper .stacktrace .trace .lines div {
        white-space: pre;
        color: #E6E1DC;
    }

    .spiral-exception .wrapper .stacktrace .trace .lines div .number {
        display: inline-block;
        width: 50px;
        color: #757575;
    }

    .spiral-exception .wrapper .stacktrace .trace .lines div:hover {
        background-color: #404040;
    }

    .spiral-exception .wrapper .stacktrace .trace .lines div.highlighted {
        background-color: #404040;
    }

    .spiral-exception .wrapper .stacktrace .chain {
        width: 40%;
        float: right;
    }

    .spiral-exception .wrapper .stacktrace .chain .calls {
        padding: 10px 10px 10px 10px;
        margin-left: 5px;
        background-color: #2e2e2e;
        margin-bottom: 5px;
        overflow-x: auto;
        box-shadow: inset 0 0 20px rgba(0, 0, 0, .2);
    }

    .spiral-exception .wrapper .stacktrace .chain .call .function {
        font-size: 11px;
        color: #6bbdff;
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
    }

    .spiral-exception .wrapper .stacktrace .chain .dumper {
        padding-left: 5px;
        padding-bottom: 5px;
        display: none;
    }

    .spiral-exception .wrapper .stacktrace .chain .dumper .close {
        text-align: right;
        padding: 2px;
        color: #fff;
        cursor: pointer;
        font-size: 12px;
        background-color: #232323;
    }

    .spiral-exception .wrapper .stacktrace .chain .dumper .close:hover {
        background-color: #2c2c2c;
    }

    .spiral-exception .wrapper .environment .container {
        margin-bottom: 9px;
    }

    .spiral-exception .wrapper .environment .title, .spiral-exception .wrapper .messages .title {
        padding: 10px 10px 10px 5px;
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
        color: #fafafa;
    }
</style>