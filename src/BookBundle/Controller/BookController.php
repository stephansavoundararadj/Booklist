<?php

namespace BookBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use BookBundle\Entity\Book;

class BookController extends Controller
{
	public function indexAction()
	{
		return $this->render('BookBundle:Book:index.html.twig');
	}
	
	public function addBookAction(Request $request)
	{
		$book = new Book();

		$formBuilder = $this->createFormBuilder($book);
		$formBuilder->add('isbn', 'text');
		
		$form = $formBuilder->getForm();
		
		$method = $request->getMethod();
		
		if ($method == "POST")
		{
			$form->bind($request);
			
			// Check if the form is valid
			if ($form->isValid())
			{
				$isbn = $_POST['form']['isbn'];
				
				// e.g.: $isbn = '9782253059530';
				$url = 'https://www.googleapis.com/books/v1/volumes?q=isbn:'.$isbn;
				$response = file_get_contents($url);
				$results = json_decode($response, true);

				if ($results['totalItems'] != 0)
				{
					// Get the global information of the novel
					$globalInfo = $results['items'][0]['volumeInfo'];
					
					// Create an array $info which will contain the information of the novel
					$info['isbn'] = $globalInfo['industryIdentifiers'][0]['identifier'];
					$info['titre'] = $globalInfo['title'];
					$info['auteur'] = $globalInfo['authors'][0];
					$info['date_publication'] = $globalInfo['publishedDate'];
					$info['description'] = $globalInfo['description'];
					$info['nombre_pages'] = $globalInfo['pageCount'];
					
					return $this->render('BookBundle:Book:get-book-info.html.twig', array('info' => $info));
				}
			}
		}
		
		return $this->render('BookBundle:Book:add-book.html.twig', array('form' => $form->createView()));
	}
	
	public function getBookDetailAction($book_id)
	{
		// Get the entity manager
		$em = $this->getDoctrine()->getManager();
	
		// Get the repository of Roman
		$repositoryRoman = $em->getRepository('BookBundle:Book');
	
		$book = $repositoryRoman->find($book_id);
	
		$isbn = $book->getIsbn();
	
		$request = 'https://www.googleapis.com/books/v1/volumes?q=isbn:'.$isbn;
		$response = file_get_contents($request);
		$results = json_decode($response, true);
	
		// Get the global information of the novel
		$globalInfo = $results['items'][0]['volumeInfo'];
	
		// Create an array $info which will contain the information of the novel
		$info['isbn'] = $globalInfo['industryIdentifiers'][0]['identifier'];
		$info['titre'] = $globalInfo['title'];
		$info['auteur'] = $globalInfo['authors'][0];
		$info['date_publication'] = $globalInfo['publishedDate'];
		$info['description'] = $globalInfo['description'];
		$info['nombre_pages'] = $globalInfo['pageCount'];
	
		return $this->render('BookBundle:Book:get-book-detail.html.twig', array('info' => $info));
	}
	
	public function validateBookAction($book_isbn)
	{
		$book = new Book();
		$book->setIsbn($book_isbn);
		
		$request = 'https://www.googleapis.com/books/v1/volumes?q=isbn:'.$book_isbn;
		$response = file_get_contents($request);
		$results = json_decode($response, true);
		
		// Get the global information of the novel
		$globalInfo = $results['items'][0]['volumeInfo'];
		$info['titre'] = $globalInfo['title'];
		
		// Set the title
		$book->setTitle($info['titre']);
		
		// Get the entity manager
		$em = $this->getDoctrine()->getManager();

		//We save the novel
		$em->persist($book);
		
		$em->flush();
		
		return $this->render('BookBundle:Book:index.html.twig');
	}
	
	public function viewBookAction()
	{
		// Get the entity manager
		$em = $this->getDoctrine()->getManager();
	
		// Get the repository
		$repositoryBook = $em->getRepository("BookBundle:Book");
	
		$bookList = $repositoryBook->findAll();
	
		return $this->render('BookBundle:Book:view-book.html.twig', array('book_list' => $bookList));
	}
}
