<div class="form-group">
    <label>ISBN</label>
    <input type="text" name="isbn" class="form-control" required>
</div>

<div class="form-group">
    <label>Tahun Terbit</label>
    <input type="number" name="tahun_terbit" class="form-control" required>
</div>

<div class="form-group">
    <label>Bahasa</label>
    <select name="bahasa" class="form-control" required>
        <option value="Indonesia">Indonesia</option>
        <option value="Inggris">Inggris</option>
        <option value="Arab">Arab</option>
        <option value="Lainnya">Lainnya</option>
    </select>
</div>

<div class="form-group">
    <label>Kata Kunci</label>
    <input type="text" name="kata_kunci" class="form-control" 
           placeholder="Pisahkan dengan koma (,)" required>
</div>

<div class="form-group">
    <label>Preview Konten</label>
    <textarea name="preview_content" class="form-control" rows="4"></textarea>
</div> 