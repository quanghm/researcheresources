<table border="0" width="100%" align="center">
	  <tr bgcolor="#CCCC66" align="center">
    		<td width="20%" nowrap="nowrap" ><?php echo "<a href=\"index.php\" class=\"menu\">"?><span class="menu">Trang ch&#7911;</span><?php echo"</a>"; ?></td>
		<td width="20%">
			<?php 
			if (logged_in())
			{
				echo "<a href=\"account.php\" class=\"menu\">H&#7891; s&#417; c&aacute; nh&acirc;n</a>";
			}
			else
			{
				echo "<a href=\"register.php\" class=\"menu\"> &#272;&#259;ng k&yacute; th&agrave;nh vi&ecirc;n</a>";
			}
			?>	</td>
    		<td width="20%" ><a href="/blogger/blog.html" class="menu">Blog</a> </td>
    		<td width="20%" ><?php echo "<a href=\"feedback.php\" class=\"menu\">G&oacute;p &yacute;</a>"; ?></td>	  
    		<td width="20%" height="40"> <?php echo "<a href=\"about.php\" class=\"menu\">V&#7873; ch&uacute;ng t&ocirc;i</a>"; ?></td>
    	  </tr>
	</table>
