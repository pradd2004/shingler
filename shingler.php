<?php

class Shingler
{
	private $minWords;
	private $text;
	private $words;
	private $wordsAmount;

	private static $stopWords = array(
		'���', "����", "����", "�", "��", "������", "���", "���", "��", "��", "��", "���", "��-��", "��-���", "�", "��", "�����", "�����", "���", "��", "���", "����", "�", "��", "���", "��", "���", "�����", "������", '����', '�����', '�o', '���', '����', '���', '���', '����', '�', '��', '������', '�����', '�', '�����', '����', "�� ��", "�� ���", '�', '�', '��', '��', '���', '�����', '����', '�����', '����', '������', '�������', '����', '�������', '��', '����', '���-������', '���-����', '���-��', '���-���', '���-��', '��� ������', '��� ����', '��� ��', '��� ���', '��� ��', '�����', '�������', '��', '��� ��', '���', '����', '���', '��', '�', '����', '���', '��', '��', '��', '������', '����', '��', '���', '��', '��', '���', '��', '����', '�����', '��', '�������', '��', '�����', '����', '�����', '��', '���', '����������', '��������', '��', '��������', '�������', '���������', '�', '������', '��', '��', '��', '��', '��', '��', '�'
	);
	
	private static $same = array(
		'�' => '�', '�' => '�',
	);

	private function replaceSameLetters()
	{
		$this->text = str_replace(array_keys(self::$same), array_values(self::$same), $this->text);
		return $this;
	}

	private function normalize()
	{
		$this->prepareText()
			->removeStopWords()
			->removeNonLetters()
			->replaceSameLetters()
			->removeControlChars();
	}
	
	private function prepareText() 
	{
		$this->text = trim(strtolower($this->text));
		return $this;
	}

	private function removeStopWords()
	{
		$pattern = '/\b(?:' . join('|', self::$stopWords) . ')\b/i';
		$this->text = preg_replace($pattern, '', $this->text);
		return $this;
	}

	private function splitByWords()
	{
		$this->text = preg_split('/[^a-zA-Z�-��-߿���������]+/', $this->text, null, PREG_SPLIT_NO_EMPTY);
		return $this;
	}

	private function removeControlChars()
	{
		$this->text = preg_replace('/\s+/', ' ', $this->text);
		return $this;
	}

	private function removeNonLetters()
	{
		$pattern = '/[^�-��-�a-zA-Z����������\s]/';
		$replacement = ' ';
		$this->text = preg_replace($pattern, $replacement, $this->text);
		return $this;
	}

	public function __construct($minWords = 3, $text = '')
	{
		$minWords = (int) $minWords;
		$minWords = $minWords > 0 ? $minWords : 3;
		$this->minWords = $minWords;
		$this->text = (string) $text;
		
		$this->normalize();
		
		$this->words = explode(' ', $this->text, -1);
		$this->wordsAmount = count($this->words);
	}

	/*
	 * Generate all possible shingles from text based on $this->minWords amount. Shingle's difference is 1 word
	 * Example with minWords = 3: 
	 * $text = "Search the world information including webpages images videos"
	 *  Search the world,
	 *  the world information,
	 *  world information including,
	 *  information including webpages,
	 *  including webpages images,
	 *  webpages images videos
	 */

	public function generateShingles($writeTail = false)
	{
		$shingles = array();
		
		if (empty($this->text) || $this->wordsAmount == 0) {
			return $shingles;
		}
		
		if ($writeTail && $this->wordsAmount <= $this->minWords) {
			return implode('', $this->words);
		}
		
		$wordsSequence = '';
		for ($i = 0; $i <= $this->wordsAmount - $this->minWords; $i++) {
			$wordsSequence = '';
			for ($j = $this->minWords; $j > 0; $j--) {
				$wordPos = $i + $this->minWords - $j;
				$wordsSequence .= $this->words[$wordPos];
			}
			$shingles[] = $wordsSequence;
		}
		if ($writeTail && !empty($wordsSequence)) {
			$shingles[] = $wordsSequence;
		}
		return $shingles;
	}

	
	/*
	 * Generate lapped shingles from text based on $this->minWords amount. Shingle's difference is last word
	 * Example with minWords = 4: 
	 * $text = "Search the world information including webpages images videos"
	 *  Search the world information,
	 *  information including webpages images,
	 */
	public function generateLappedShingles($writeTail = false)
	{
		$shingles = array();
		if (empty($this->text) || $this->wordsAmount <= $this->minWords) {
			return $shingles;
		}
		$wordsSequence = '';
		for ($i = 0; $i <= $this->wordsAmount - $this->minWords; $i += $this->minWords - 1) {
			$wordsSequence = '';
			for ($j = 0; $j < $this->minWords; $j++) {
				$wordPos = $i + $j;
				$wordsSequence .= $this->words[$wordPos];
			}
			$shingles[] = $wordsSequence;
		}
		if ($writeTail && !empty($wordsSequence)) {
			$shingles[] = $wordsSequence;
		}
		return $shingles;
	}

	/*
	 * Generate joint shingles from text based on $this->minWords amount.
	 * Example with minWords = 3: 
	 * $text = "Search the world information including webpages images videos"
	 *  Search the world,
	 *  information including webpages,
	 *  images videos
	 */

	public function generateJointShingles($writeTail = false)
	{
		$shingles = array();
		$wordsSequence = '';
		for ($i = 0; $i < $this->wordsAmount; $i++) {
			$wordsSequence .= $this->words[$i];
			if ($i % $this->minWords == $this->minWords - 1 && !empty($wordsSequence)) {
				$shingles[] = $wordsSequence;
				$wordsSequence = '';
			}
			if ($writeTail && !empty($wordsSequence) && $i == $this->wordsAmount - 1) {
				$shingles[] = $wordsSequence;
			}
		}
		return $shingles;
	}

}
