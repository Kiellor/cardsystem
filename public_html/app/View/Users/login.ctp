<div class="users form" style="width:100%; height:100%; font-size:26px;">
    <?php echo $this->Session->flash('auth'); ?>

    <form action="login" id="UserLoginForm" method="post" accept-charset="utf-8">
        <input type="hidden" name="_method" value="POST"/>
        <table style="margin: 0 auto;">
            <tr>
                <td>Email</td>
                <td><input name="data[User][username]" size="40" id="UserUsername"/></td>
            </tr>
            <tr>
                <td>Password</td>
                <td><input name="data[User][password]" size="40" type="password" id="UserPassword"/></td>
            </tr>
        </table>
        <div class="submit" style="width:100%; text-align:center;">
            <input style="margin: 0 auto;" type="submit" value="Login"/><br/>
            <a href="reset">forgot your password?</a>
        </div>
    </form>

</div>