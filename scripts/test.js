        let postData = {
            NICK: "test",
            PASSWORD: "test",
            title: "test",
            art: "test"
        };

        //let result = await CURL(
        //    "server_side/ascii_post.php",
        //    postData
        //);

        var result = await CURL('https://amjp.psy-k.org/JPLY_BBS/server_side/ascii_post.php', {
            data: 'NICK=test&PASSWORD=test&title=test+&art=test'
        });

    PRINT("===== DEBUG RESULT =====");
    PRINT(result);
