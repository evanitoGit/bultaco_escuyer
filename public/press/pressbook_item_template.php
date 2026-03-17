<div class="pressbook-admin-card">
    <?php if ($item['image_path']): ?>
        <img src="<?php echo htmlspecialchars($item['image_path']); ?>" alt="<?php echo htmlspecialchars($item['titre']); ?>">
    <?php else: ?>
        <div class="no-image-admin-press">
            <?php
            $icons = [
                'article' => '📰',
                'magazine' => '📔',
                'photo' => '📸',
                'logo' => '🎨',
                'illustration' => '✏️'
            ];
            echo $icons[$item['type_contenu']];
            ?>
        </div>
    <?php endif; ?>

    <div class="pressbook-admin-info">
        <span class="pressbook-type-badge"><?php echo strtoupper($item['type_contenu']); ?></span>
        <h3><?php echo htmlspecialchars($item['titre']); ?></h3>

        <?php if ($item['date_publication']): ?>
            <p class="pressbook-meta">📅 <?php echo htmlspecialchars($item['date_publication']); ?></p>
        <?php endif; ?>

        <?php if ($item['source']): ?>
            <p class="pressbook-meta">📰 <?php echo htmlspecialchars($item['source']); ?></p>
        <?php endif; ?>

        <?php if ($item['description']): ?>
            <p class="pressbook-description"><?php echo htmlspecialchars(substr($item['description'], 0, 100)) . (strlen($item['description']) > 100 ? '...' : ''); ?></p>
        <?php endif; ?>

        <div class="pressbook-admin-actions">
            <button onclick="toggleEditItem(<?php echo $item['id']; ?>)" class="btn">✏️ Modifier</button>
            <form method="POST" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet élément ?');">
                <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                <button type="submit" name="supprimer_item" class="btn btn-danger">🗑️ Supprimer</button>
            </form>
        </div>

        <div class="edit-form-press" id="edit-item-<?php echo $item['id']; ?>" style="display: none;">
            <form method="POST">
                <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">

                <div class="form-group">
                    <label class="form-label">Type :</label>
                    <select name="nouveau_type" required>
                        <option value="article" <?php echo $item['type_contenu'] === 'article' ? 'selected' : ''; ?>>Article</option>
                        <option value="magazine" <?php echo $item['type_contenu'] === 'magazine' ? 'selected' : ''; ?>>Magazine</option>
                        <option value="photo" <?php echo $item['type_contenu'] === 'photo' ? 'selected' : ''; ?>>Photo</option>
                        <option value="logo" <?php echo $item['type_contenu'] === 'logo' ? 'selected' : ''; ?>>Logo</option>
                        <option value="illustration" <?php echo $item['type_contenu'] === 'illustration' ? 'selected' : ''; ?>>Illustration</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Titre :</label>
                    <input type="text" name="nouveau_titre" value="<?php echo htmlspecialchars($item['titre']); ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Date :</label>
                    <input type="text" name="nouvelle_date" value="<?php echo htmlspecialchars($item['date_publication']); ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Source :</label>
                    <input type="text" name="nouvelle_source" value="<?php echo htmlspecialchars($item['source']); ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Description :</label>
                    <textarea name="nouvelle_description"><?php echo htmlspecialchars($item['description']); ?></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Lien externe :</label>
                    <input type="url" name="nouveau_lien" value="<?php echo htmlspecialchars($item['lien_externe']); ?>">
                </div>

                <button type="submit" name="modifier_item" class="btn">💾 Enregistrer</button>
            </form>
        </div>
    </div>
</div>