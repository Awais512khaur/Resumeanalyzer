<?php

/**
 * Write one auditable event without allowing a logging outage to interrupt
 * the application action that has already completed.
 */
function audit_log(
    PDO $pdo,
    string $action,
    ?string $details = null,
    ?string $entityType = null,
    ?int $entityId = null,
    ?int $actorId = null,
    ?string $actorName = null
): void {
    $actorId = $actorId !== null ? $actorId : (isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null);
    $actorName = $actorName !== null ? $actorName : ($_SESSION['user_name'] ?? null);
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
    $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? substr((string)$_SERVER['HTTP_USER_AGENT'], 0, 255) : null;

    try {
        $statement = $pdo->prepare(
            'INSERT INTO audit_logs (actor_id, actor_name, action, entity_type, entity_id, details, ip_address, user_agent)
             VALUES (:actor_id, :actor_name, :action, :entity_type, :entity_id, :details, :ip_address, :user_agent)'
        );
        $statement->execute([
            ':actor_id' => $actorId,
            ':actor_name' => $actorName !== null ? substr((string)$actorName, 0, 150) : null,
            ':action' => substr($action, 0, 100),
            ':entity_type' => $entityType !== null ? substr($entityType, 0, 80) : null,
            ':entity_id' => $entityId,
            ':details' => $details !== null ? substr($details, 0, 2000) : null,
            ':ip_address' => $ipAddress !== null ? substr((string)$ipAddress, 0, 45) : null,
            ':user_agent' => $userAgent,
        ]);
    } catch (Throwable $exception) {
        error_log('Resume Analyzer audit logging failed: ' . $exception->getMessage());
    }
}
