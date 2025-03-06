<?php

/** PHP rewrite of includes/sort.asp **/

/*
Sub QuickSort(vec,loBound,hiBound,SortField,SortDir)
  '==--------------------------------------------------------==
  '== Sort a multi dimensional array on SortField            ==
  '==                                                        ==
  '== This procedure is adapted from the algorithm given in: ==
  '==    ~ Data Abstractions & Structures using C++ by ~     ==
  '==    ~ Mark Headington and David Riley, pg. 586    ~     ==
  '== Quicksort is the fastest array sorting routine for     ==
  '== unordered arrays.  Its big O is n log n                ==
  '==                                                        ==
  '== Parameters:                                            ==
  '== vec       - array to be sorted                         ==
  '== SortField - The field to sort on (1st dimension value) ==
  '== loBound and hiBound are simply the upper and lower     ==
  '==   bounds of the array's "row" dimension. It's probably ==
  '==   easiest to use the LBound and UBound functions to    ==
  '==   set these.                                           ==
  '== SortDir   - ASC, ascending; DESC, Descending           ==
  '==--------------------------------------------------------==
  */

// dim arr(400,8)  UNLIKE PHP, the numbers 400 and 8 are the
// HIGHEST INDEX, not the number of elements(since zero is NOT counted)!
// so Ubound(arr, 1) would return 400, not 401, as I thought before
// if ($idx > 0) { eg. 1?
// QuickSort arr, 0, idx, COL_DATEVAL, "DESC"
//quickSort($arr, 0, $idx, COL_DATEVAL = 5, 'DESC');
//}

function quickSort(array &$arr, $loBound, $hiBound, $sortField, $sortDir)
{
    //if not (hiBound - loBound = 0) then
    if (($hiBound - $loBound) !== 0) {
        //Dim pivot(),loSwap,hiSwap,temp,counter
        //Redim pivot (Ubound(vec,2))
        // so, change $pivot's size, using the highest index 
        // in second dimension of $arr array
        $dim2LastIdx = count($arr[$hiBound]) - 1;
        $pivot       = array_fill(0, $dim2LastIdx, '');

        $sortDir = strtoupper($sortDir);

        //'== Two items to sort
        //if hiBound - loBound = 1 then
        if ($hiBound - $loBound === 1) {
            if ($sortDir == "ASC") {
                if (formatCompare($arr[$loBound][$sortField], $arr[$hiBound][$sortField]) > formatCompare($arr[$hiBound][$sortField], $arr[$loBound][$sortField])) {
                    swapRows($arr, $hiBound, $loBound);
                }
            } else {
                if (formatCompare($arr[$loBound][$sortField], $arr[$hiBound][$sortField]) < formatCompare($arr[$hiBound][$sortField], $arr[$loBound][$sortField])) {
                    swapRows($arr, $hiBound, $loBound);
                }
            }
        }

        //'== Three or more items to sort
        for ($counter = 0; $counter <= $dim2LastIdx; $counter++) {
            $pivot[$counter] = $arr[intval(($loBound + $hiBound) / 2)][$counter];
            $arr[intval(($loBound + $hiBound) / 2)][$counter] = $arr[$loBound][$counter];
            $arr[$loBound][$counter] = $pivot[$counter];
        }

        $loSwap = $loBound + 1;
        $hiSwap = $hiBound;

        do {
            //'== Find the right loSwap
            if ($sortDir == "ASC") {
                while ($loSwap < $hiSwap && formatCompare($arr[$loSwap][$sortField], $pivot[$sortField]) <= formatCompare($pivot[$sortField], $arr[$loSwap][$sortField])) {
                    $loSwap = $loSwap + 1;
                }
            } else {

                while ($loSwap < $hiSwap && formatCompare($arr[$loSwap][$sortField], $pivot[$sortField]) >= formatCompare($pivot[$sortField], $arr[$loSwap][$sortField])) {
                    $loSwap = $loSwap + 1;
                }
            }

            //'== Find the right hiSwap
            if ($sortDir == "ASC") {
                while (formatCompare($arr[$hiSwap][$sortField], $pivot[$sortField]) > formatCompare($pivot[$sortField], $arr[$hiSwap][$sortField])) {
                    $hiSwap = $hiSwap - 1;
                }
            } else {
                while (formatCompare($arr[$hiSwap][$sortField], $pivot[$sortField]) < formatCompare($pivot[$sortField], $arr[$hiSwap][$sortField])) {
                    $hiSwap = $hiSwap - 1;
                }
            }

            //'== Swap values if loSwap is less then hiSwap
            if ($loSwap < $hiSwap) {
                swapRows($arr, $loSwap, $hiSwap);
            }
        } while ($loSwap < $hiSwap);

        for ($counter = 0; $counter <= $dim2LastIdx; $counter++) {
            $arr[$loBound][$counter] = $arr[$hiSwap][$counter];
            $arr[$hiSwap][$counter] = $pivot[$counter];
        }

        //'== Recursively call function .. the beauty of Quicksort
        //'== 2 or more items in first section
        if ($loBound < ($hiSwap - 1)) {
            quickSort($arr, $loBound, ($hiSwap - 1), $sortField, $sortDir);
        }
        //'== 2 or more items in second section
        if (($hiSwap + 1) < $hiBound) {
            quickSort($arr, ($hiSwap + 1), $hiBound, $sortField, $sortDir);
        }
    }
} //End Sub  'QuickSort


//'== This proc swaps two rows of an array ==
function swapRows(array &$arr, $row1, $row2): void
{
    $tempVar   = null;
    $lastIndex = count($arr[$row1]) - 1;

    for ($x = 0; $x <= $lastIndex; $x++) {
        $tempVar        = $arr[$row1][$x];
        $arr[$row1][$x] = $arr[$row2][$x];
        $arr[$row2][$x] = $tempVar;
    }
}

//'==  Checks sOne & sTwo, returns sOne as a   ==
//'==  Numeric if both pass isNumeric, if not  ==
//'==  returns sOne as a string.               ==
function formatCompare($sOne, $sTwo)
{
    if (is_numeric(trim($sOne)) && is_numeric(trim($sTwo))) {
        return floatval((trim($sOne)));
    } else {
        return trim($sOne);
    }
}

// Sub PrintArray(vec,loRow,hiRow,markCol)
//'== Print out an array  Highlight the column ==
//'==  whose number matches param markCol      ==
function printArray(array $arr, $loRow, $hiRow, $markCol): void
{
    $arrCount  = count($arr);
    $lastIndex = ($arrCount > 0) ? $arrCount - 1 : 0;
    $secondDim = $arr[$lastIndex]; // 2nd array dimension

    if (is_array($secondDim)) {
        for ($i = $loRow; $i <= $hiRow; $i++) {

            $tableElem = '<table border="1" cellspacing="0">';
            foreach ($secondDim as $col) {
                $tableElem .= '<tr>';
                $tdElem = ($col === $markCol) ? '<td bgcolor="#FFFFCC">' : '<td>';
                $tableElem .= $tdElem . $arr[$i][$col] . '</td>';
                $tableElem .= '</tr>';
            }
            $tableElem .= '</table>';

            echo $tableElem; // Output the table
        }
    }

    echo ''; // No table created
}
