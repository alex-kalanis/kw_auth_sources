<?php

namespace kalanis\kw_auth_sources\Sources\Files;


use kalanis\kw_accounts\AccountsException;
use kalanis\kw_accounts\Data\FileGroup;
use kalanis\kw_accounts\Interfaces as acc_interfaces;
use kalanis\kw_auth_sources\AuthSourcesException;
use kalanis\kw_auth_sources\Interfaces;
use kalanis\kw_auth_sources\Traits;
use kalanis\kw_locks\Interfaces\ILock;
use kalanis\kw_locks\LockException;


/**
 * Class Groups
 * @package kalanis\kw_auth_sources\Sources\Files
 * Work with groups of users
 */
class Groups implements acc_interfaces\IProcessGroups
{
    use Traits\TAuthLock;
    use Traits\TLines;
    use Traits\TSeparated;
    use Traits\TStatusTransform;

    // default positions
    protected const GRP_ID = 0;
    protected const GRP_NAME = 1;
    protected const GRP_AUTHOR = 2;
    protected const GRP_DESC = 3;
    protected const GRP_STATUS = 4;
    protected const GRP_PARENTS = 5;
    protected const GRP_EXTRA = 6;
    protected const GRP_FEED = 7;

    protected Storages\AStorage $storage;
    protected acc_interfaces\IProcessAccounts $accounts;
    protected Interfaces\IExtraParser $extraParser;
    /** @var string[] */
    protected array $path = [];

    /**
     * @param Storages\AStorage $storage
     * @param acc_interfaces\IProcessAccounts $accounts
     * @param Interfaces\IExtraParser $parser
     * @param ILock $lock
     * @param string[] $path
     * @param Interfaces\IKAusTranslations|null $lang
     */
    public function __construct(
        Storages\AStorage $storage,
        acc_interfaces\IProcessAccounts $accounts,
        Interfaces\IExtraParser $parser,
        ILock $lock,
        array $path,
        ?Interfaces\IKAusTranslations $lang = null
    )
    {
        $this->setAusLang($lang);
        $this->initAuthLock($lock);
        $this->storage = $storage;
        $this->path = $path;
        $this->accounts = $accounts;
        $this->extraParser = $parser;
    }

    public function createGroup(acc_interfaces\IGroup $group): bool
    {
        $userId = $group->getGroupAuthorId();
        $groupName = $this->stripChars($group->getGroupName());
        $groupDesc = $this->stripChars($group->getGroupDesc());

        // not everything necessary is set
        if (empty($userId) || empty($groupName)) {
            throw new AccountsException($this->getAusLang()->kauGroupMissParam());
        }

        try {
            $this->checkLock();

            $gid = 0;
            $this->getLock()->create();

            // read groups
            try {
                $groupLines = $this->openGroups();
            } catch (AuthSourcesException $ex) {
                // silence the problems on storage
                $groupLines = [];
            }
            foreach ($groupLines as &$line) {
                $gid = max($gid, $line[static::GRP_ID]);
            }
            $gid++;

            $newGroup = [
                static::GRP_ID => $gid,
                static::GRP_NAME => $groupName,
                static::GRP_AUTHOR => $userId,
                static::GRP_DESC => !empty($groupDesc) ? $groupDesc : $groupName,
                static::GRP_STATUS => $group->getGroupStatus(),
                static::GRP_PARENTS => $this->compactStr($group->getGroupParents()),
                static::GRP_EXTRA => $this->extraParser->compact($group->getGroupExtra()),
                static::GRP_FEED => '',
            ];
            ksort($newGroup);
            $groupLines[] = $newGroup;

            try {
                // now save it
                $result = $this->saveGroups($groupLines);
            } finally {
                $this->getLock()->delete();
            }
            return $result;

        } catch (AuthSourcesException | LockException $ex) {
            throw new AccountsException($ex->getMessage(), $ex->getCode(), $ex);
        }
    }

    public function getGroupDataOnly(string $groupId): ?acc_interfaces\IGroup
    {
        try {
            $this->checkLock();
            try {
                $groupLines = $this->openGroups();
            } catch (AuthSourcesException $ex) {
                // silence the problems on storage
                return null;
            }
            foreach ($groupLines as &$line) {
                if ($line[static::GRP_ID] == $groupId) {
                    return $this->getGroupClass($line);
                }
            }
            return null;

        } catch (AuthSourcesException | LockException $ex) {
            throw new AccountsException($ex->getMessage(), $ex->getCode(), $ex);
        }
    }

    public function readGroup(): array
    {
        try {
            $this->checkLock();

            $groupLines = $this->openGroups();
            $result = [];
            foreach ($groupLines as &$line) {
                $result[] = $this->getGroupClass($line);
            }

            return $result;

        } catch (AuthSourcesException | LockException $ex) {
            throw new AccountsException($ex->getMessage(), $ex->getCode(), $ex);
        }
    }

    /**
     * @param array<int, string> $line
     * @throws AuthSourcesException
     * @return acc_interfaces\IGroup
     */
    protected function getGroupClass(array &$line): acc_interfaces\IGroup
    {
        $group = new FileGroup();
        $group->setGroupData(
            strval($line[static::GRP_ID]),
            strval($line[static::GRP_NAME]),
            strval($line[static::GRP_DESC]),
            strval($line[static::GRP_AUTHOR]),
            intval($line[static::GRP_STATUS]),
            $this->separateStr($line[static::GRP_PARENTS]),
            $this->extraParser->expand($line[static::GRP_EXTRA])
        );
        return $group;
    }

    public function updateGroup(acc_interfaces\IGroup $group): bool
    {
        $groupName = $this->stripChars($group->getGroupName());
        $groupDesc = $this->stripChars($group->getGroupDesc());

        try {
            $this->checkLock();

            $this->getLock()->create();
            try {
                $groupLines = $this->openGroups();
            } finally {
                $this->getLock()->delete();
            }
            foreach ($groupLines as &$line) {
                if ($line[static::GRP_ID] == $group->getGroupId()) {
                    // REFILL
                    $line[static::GRP_NAME] = !empty($groupName) ? $groupName : $line[static::GRP_NAME] ;
                    $line[static::GRP_DESC] = !empty($groupDesc) ? $groupDesc : $line[static::GRP_DESC] ;
                    $line[static::GRP_STATUS] = $group->getGroupStatus();
                    $line[static::GRP_PARENTS] = $this->compactStr($group->getGroupParents());
                    $line[static::GRP_EXTRA] = $this->extraParser->compact($group->getGroupExtra());
                }
            }

            try {
                $result = $this->saveGroups($groupLines);
            } finally {
                $this->getLock()->delete();
            }
            return $result;

        } catch (AuthSourcesException | LockException $ex) {
            throw new AccountsException($ex->getMessage(), $ex->getCode(), $ex);
        }
    }

    public function deleteGroup(string $groupId): bool
    {
        try {
            $this->checkLock();
            $this->checkRest($groupId);

            $changed = false;
            $this->getLock()->create();

            // update groups
            try {
                $openGroups = $this->openGroups();
            } catch (AuthSourcesException $ex) {
                // silence the problems on storage
                $this->getLock()->delete();
                return false;
            }
            foreach ($openGroups as $index => &$line) {
                if ($line[static::GRP_ID] == $groupId) {
                    unset($openGroups[$index]);
                    $changed = true;
                }
            }

            $result = true;
            try {
                // now save it
                if ($changed) {
                    $result = $this->saveGroups($openGroups);
                }
            } finally {
                $this->getLock()->delete();
            }
            return $changed && $result;

        } catch (AuthSourcesException | LockException $ex) {
            throw new AccountsException($ex->getMessage(), $ex->getCode(), $ex);
        }
    }

    /**
     * @param string $groupId
     * @throws AccountsException
     */
    protected function checkRest(string $groupId): void
    {
        $passLines = $this->accounts->readAccounts();
        foreach ($passLines as &$line) {
            if ($line->getGroup() == $groupId) {
                throw new AccountsException($this->getAusLang()->kauGroupHasMembers());
            }
        }
    }

    /**
     * @throws AuthSourcesException
     * @return array<int, array<int, string|int>>
     */
    protected function openGroups(): array
    {
        return $this->storage->read(array_merge($this->path, [Interfaces\IFile::GROUP_FILE]));
    }

    /**
     * @param array<int, array<int, string|int>> $lines
     * @throws AuthSourcesException
     * @return bool
     */
    protected function saveGroups(array $lines): bool
    {
        return $this->storage->write(array_merge($this->path, [Interfaces\IFile::GROUP_FILE]), $lines);
    }

    /**
     * @return string
     * @codeCoverageIgnore translation
     */
    protected function noDirectoryDelimiterSet(): string
    {
        return $this->getAusLang()->kauNoDelimiterSet();
    }
}
