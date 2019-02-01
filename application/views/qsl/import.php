<div id="container">
<h2><?php echo $page_title; ?></h2>
<?php $this->load->view('layout/messages'); ?>

<?php echo form_open_multipart('qsl/import');?>
<table>
        <tr>
                <td><input type="radio" name="qslimport" id="upload" value="upload" checked /> Upload a file</td>
                <td>
                        <p>Upload the Exported ADIF file from your logging program</p>
                        <p><span class="label important">Important</span> Log files must have the file type .adi</p>
                        <input type="file" name="userfile" size="20" />
                </td>
        </tr>
</table>
<input class="btn primary" type="submit" value="Import" />

</form>

</div>



