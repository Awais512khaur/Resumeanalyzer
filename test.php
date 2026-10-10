  <!-- MATCHED -->
            <p class="text-green-400 text-sm mb-1">Matched:</p>
            <div class="flex flex-wrap gap-2 mb-3">
                <?php foreach ($matched as $m): ?>
                    <?php if (!empty($m)): ?>
                        <span class="bg-green-500/20 px-2 py-1 text-xs rounded"><?php echo $m; ?></span>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <!-- MISSING -->
            <p class="text-red-400 text-sm mb-1">Missing:</p>
            <div class="flex flex-wrap gap-2 mb-3">
                <?php foreach ($missing as $m): ?>
                    <?php if (!empty($m)): ?>
                        <span class="bg-red-500/20 px-2 py-1 text-xs rounded"><?php echo $m; ?></span>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <!-- 🔥 SUGGESTIONS -->
            <p class="text-yellow-400 text-sm mb-1">Suggestions:</p>
            <ul class="text-gray-300 text-sm list-disc ml-5">
                <?php foreach (explode(" | ", $r['suggestions']) as $s): ?>
                    <li><?php echo $s; ?></li>
                <?php endforeach; ?>
            </ul>