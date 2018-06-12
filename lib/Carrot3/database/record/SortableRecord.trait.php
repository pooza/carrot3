<?php
namespace Carrot3;

trait SortableRecord {
	protected $next;
	protected $prev;
	protected $similars;

	public function getPrev () {
		if (!$this->prev) {
			$iterator = $this->getSimilars()->getIterator();
			foreach ($iterator as $record) {
				if ($this->getID() == $record->getID()) {
					return $this->prev = $iterator->prev();
				}
			}
		}
		return $this->prev;
	}

	public function getNext () {
		if (!$this->next) {
			$iterator = $this->getSimilars()->getIterator();
			foreach ($iterator as $record) {
				if ($this->getID() == $record->getID()) {
					return $this->next = $iterator->next();
				}
			}
		}
		return $this->next;
	}

	protected function isUpdatable ():bool {
		return true;
	}

	protected function isDeletable ():bool {
		return true;
	}

	protected function getSimilars () {
		if (!$this->similars) {
			$this->similars = TableHandler::create(Utils::getShortClass($this));
			if ($record = $this->getParent()) {
				$this->similars->getCriteria()->register(
					$record->getTable()->getName() . '_id',
					$record
				);
			}
		}
		return $this->similars;
	}

	public function setOrder ($option) {
		$rank = 0;
		foreach ($ids = $this->getSimilars()->getIDs() as $id) {
			if ($id == $this->getID()) {
				break;
			}
			$rank ++;
		}

		switch ($option) {
			case 'up':
				if ($ids[$rank - 1]) {
					$ids[$rank] = $ids[$rank - 1];
					$ids[$rank - 1] = $this->getID();
				}
				break;
			case 'down':
				if ($ids[$rank + 1]) {
					$ids[$rank] = $ids[$rank + 1];
					$ids[$rank + 1] = $this->getID();
				}
				break;
			case 'top':
				$ids->removeParameter($rank);
				$ids->unshift($this->getID());
				break;
			case 'bottom':
				$ids->removeParameter($rank);
				$ids[] = $this->getID();
				break;
		}

		$rank = 0;
		foreach ($ids as $id) {
			$rank ++;
			if ($record = $this->getSimilars()->getRecord($id)) {
				$record->setRank($rank);
			}
		}
	}

	protected function setRank (int $rank) {
		$record = $this;
		$values = [$record->getTable()->getRankField() => $rank];
		while (true) {
			$this->getDatabase()->exec(SQL::getUpdateQuery(
				$record->getTable(),
				$values,
				$record->createCriteria()
			));
			if (!$record = $record->getParent()) {
				break;
			}
			$values = [
				$record->getTable()->getUpdateDateField() => Date::create()->format('Y-m-d H:i:s'),
			];
		}
	}
}
