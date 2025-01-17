<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

use App\Models\table_anime;
use App\Models\table_assistindo;
use App\Models\table_ranking;
use App\Models\table_continua;
use App\Models\AnimesParados;
use App\Models\users;

class ProfileController extends Controller
{
    private $id_user2;
    private $idUserSs;
    
    private function getUserInfo(){
        
        $id_user_sse = Auth::id() ?? Session::get('user_id');
        $getUserData = users::find($id_user_sse);

        return [
            'id' => $getUserData->id,
            'level_user' => $getUserData->level,
            'exp_user' => $getUserData->exp,
            'name_user' => $getUserData->name,
            'cargo_user' => $getUserData->cargo,
        ];
    }
    
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
    
    public function __construct(){
        
        $this->id_user2 = 'teste construct';
        
        /* ve porque ta retornando 0 na tela form_assistindo 
        $this->idUserSs = Auth::id() ?? Session::get('user_id');*/
        /*if (Auth::check()) {
        $this->idUserSs = Auth::id();
        } else {
            $this->idUserSs = Session::get('user_id', 0);
        }*/
        
    }
    
    public function index() {
        
        return view('welcome');
    }
    
    public function testeUser(){
        
        $usuario = auth()->user();
        $id_user_sse = $usuario->id;
        $slc_userExp = users::findOrFail($id_user_sse);
        $exp_user = $slc_userExp->exp;
        
        $teste1 = users::find(5);
        $teste2 = users::where('id', 5)->first();
        $teste3 = users::firstWhere('id', 5);
        
        /* antigo pegar dados do usuario */
        // $id_user_sse = Auth::id() ?? Session::get('user_id');
        // $level_user = users::where('id', $id_user_sse)->value('level');
        // $exp_user = users::where('id', $id_user_sse)->value('exp');
        // $name_user = users::where('id', $id_user_sse)->value('name');
        // $cargo_user = users::where('id', $id_user_sse)->value('cargo');
        
        return view('pages.teste', compact(["usuario", "id_user_sse", "teste1", "teste2", "teste3", "exp_user"]));
    }
    
    public function home(){
        
        $getUserData = $this->getUserInfo();
        
        $buscar2 = request('search2');
        $dt = date('d/m/Y');
        $dataAtual = date('Y-m-d');
        
        $session_user = auth()->user();
        $id_user_sse = $session_user->id;
        
        /* Selects table */ 
        $table_animes = table_anime::all();
        $table_continua = table_continua::with('nome_anime')->get();
        
        /* Section ranking anime*/
        $rankingAnime = table_anime::where('temporada', 'summer/julho')
            ->orderBy('nome', 'asc')
            ->take(4)
            ->get();
        $ranking10Anime = table_ranking::where('nota', 10)
            ->orderBy('episodio', 'desc')
            ->take(4)
            ->get();
        
        /* Filtrando id da sessao para colocar a exp do nivel do ususario */
        $session_user = auth()->user();
        $id_user_sse = $session_user->id;
        $nivel_usuario = users::where('id', $id_user_sse)->get();
        
        $busca = request('search');
        if($buscar2){
            $table_assistidos = table_assistindo::where([ ['dia_semana', 'like', '%'.$buscar2.'%'] ])->get();
        }else{
            $table_assistidos = table_assistindo::where('id_usuario', $id_user_sse)
                ->join('table_anime', 'table_anime.id', '=', 'table_assistindo.id_anime')
                ->orderBy('table_anime.data_semana')
                ->select('table_assistindo.*')
                ->with(['nome_anime' => function ($query) {$query->orderBy('data_semana');}])
                ->get();
        }
        
        return view('pages.home', compact(["table_assistidos", "buscar2", "table_continua", "dt", "table_animes", "busca", "dataAtual", "ranking10Anime", "rankingAnime", "getUserData"]));
    }
    
    public function formAnime(){
        
        $getUserData = $this->getUserInfo();
        $DataAtual = date('Y');
        
        $slc_animeAll = table_anime::orderBy('id', 'desc')->get();
        $table_animes = table_anime::whereJsonContains('genero', ["Fantasia"])->get();
        
        $anime = table_anime::all();
        $campos = [];
        
        foreach ($anime as $animes) {
            $campos[] = ['nome' => $animes->nome, 'id' => $animes->id];
        }
        
        return view('pages.admin.formAnime', compact(["DataAtual", "table_animes", "slc_animeAll", "getUserData", "campos"]));
    }
    
    public function animeAdd(request $request){
        
       /*dd($request->all());*/
        $dbanime = new table_anime;

        $dbanime->nome = $request->nome;
        $dbanime->estreia = $request->estreia;
        $dbanime->temporada = $request->temporada;
        $dbanime->episodio = $request->episodio;
        $dbanime->genero = $request->genero;
        $dbanime->data_semana = $request->data_semana;
        $arquivo = $request->file('img');
        
        if ($request->hasFile('img')) {

            // Certifique-se de que $arquivo é um objeto UploadedFile válido
            if ($arquivo instanceof \Illuminate\Http\UploadedFile && $arquivo->isValid()) {
                $nomeArquivo = pathinfo($arquivo->getClientOriginalName(), PATHINFO_FILENAME);
                $extensao = $arquivo->getClientOriginalExtension();
                $nomeArquivoArmazenado = $nomeArquivo . '_' . time() . '.' . $extensao;

                // Use o método store para salvar o arquivo
                $arquivo->storeAs('public/imgs', $nomeArquivoArmazenado);

            } else {
                return response()->json(['error' => 'O arquivo enviado não é válido.']);
            }
        } else {
            return response()->json(['error' => 'O campo de arquivo "image" está ausente na requisição.']);
        }
        
/*        if($request->hasFile('image') && file_exists($arquivo->getPathname()) && $arquivo->isValid()){
            $nomeArquivo = pathinfo($arquivo->getClientOriginalName(), PATHINFO_FILENAME);
            $extensao = $arquivo->getClientOriginalExtension();
            $nomeArquivoArmazenado = $nomeArquivo . '_' . time() . '.' . $extensao;
            $arquivo->storeAs('public/imgs', $nomeArquivoArmazenado);

        } else {
            dd([
                'hasFile' => $request->hasFile('image'),
                'isValid' => $request->file('image')->isValid(),
                'errors' => $request->file('image')->getErrorMessage(),
            ]);
            return response()->json(['error' => 'Erro no upload do arquivo.']);
        }*/
        
        $dbanime->save();
        
        return redirect()->route('dashboard');
    }
    
    public function animeAdd2(request $request){
        
        $tb_anime = new table_anime;
        
        $tb_anime->nome = $request->nome;
        $tb_anime->estreia = $request->estreia;
        $tb_anime->temporada = $request->temporada;
        $tb_anime->episodio = $request->episodio;
        $tb_anime->genero = $request->genero;
        $tb_anime->data_semana = $request->data_semana;
        $arquivo = $request->file('arquivo');
        
       if(isset($arquivo) || !empty($arquivo)){
            
            $nomeArquivo = pathinfo($arquivo->getClientOriginalName(), PATHINFO_FILENAME);
            $extensao = $arquivo->getClientOriginalExtension();
            $nomeArquivoArmazenado = $nomeArquivo . '_' . time() . '.' . $extensao;
            $arquivo->storeAs('public/animes', $nomeArquivoArmazenado);
            $tb_anime->image = $nomeArquivoArmazenado;
            
        } else {
            return response()->json(['error' => 'O arquivo enviado não é válido.']);
        } 
        
        $tb_anime->save();
        
        return redirect()->route('formAnime');
    }
    
    public function formassistindo(){
        
        $getUserData = $this->getUserInfo();
        
        /* Consultas */
        $table_animes = table_anime::all();
        
        $teste2 = $this->id_user2;
        $teste3 = $this->idUserSs;
        /* outra forma de pegar o id do usuario na sessao
        $session_user = auth()->user();
        $id_user_sse = $session_user->id;*/
        
        return view('pages.form-assistindo', compact(["table_animes", "teste2", "teste3", "getUserData"]));
    }
    
    public function assistindoAdd(request $request){
        
        /* Adicionando exp vinculado ao usuario apos add new anime */
        $session_user = auth()->user();
        $id_user = $session_user->id;
        users::findOrFail($id_user)->increment('exp', 1);
        
        /* Cadastrando novo anime */
        $animewatch = new table_assistindo;
        
        $animewatch->id_anime = $request->id_anime;
        $animewatch->episodio = $request->episodio;
        $animewatch->dia_semana = $request->dia_semana;
        
        $animewatch->nota = $request->nota;
        $animewatch->descricao = $request->descricao;
        $animewatch->id_usuario = $id_user;
        $animewatch->link = $request->link;

        $animewatch->save();
        return redirect('/formassistindo');
    }
    
    /* delete.update.ações */
    public function destroy_assistindo($id){
        
        table_assistindo::findOrFail($id)->delete();
        return redirect()->route('home');
    }
    
    public function destroy_parados($id){
        
        AnimesParados::findOrFail($id)->delete();
        return redirect()->route('home');
    }
    
    public function edit_assistindo($id){
        
        $nome = "jimy";
        $idade = "30";
        
        $table_assistidos = table_assistindo::findOrFail($id);
        return view('pages.edit-assistindo', ["nome" => $nome, "idade" => $idade, "table_assistidos" => $table_assistidos]);
    }
    
    public function update_assistindo(Request $request){
        
        $nome = "jimy";
        $idade = "30";
        
        table_assistindo::findOrFail($request->id)->update($request->all());
        return redirect('/');
    }
    
    public function plusanime($id_anime, $id_assist){
        
        $this->plusEpisodio($id_assist);
        $this->plusExpUser();
        $this->plusEpisodio_updateDate($id_anime);
        
        return redirect('/home')->with('error', 'Ocorreu um erro ao atualizar as tabelas.');
    }
    
    public function plusEpisodio($id_assist){
        
        table_assistindo::findOrFail($id_assist)->increment('episodio', 1);
    }
    
    public function plusExpUser(){
        
        /* Adicionando exp vinculado ao usuario apos add new anime */
        $session_user = auth()->user();
        $id_user = $session_user->id;
        users::findOrFail($id_user)->increment('exp', 1);
        
        /* verificação de exp para up level e zerar exp batendo os 100exp */
        $slc_user = users::findOrFail($id_user);
        $exp_user = $slc_user->exp;
        if($exp_user >= 100.00){
            $slc_user->increment('level', 1);
            $slc_user->decrement('exp', 100);
        }else{
            echo 'teste menor';
        }
    }
    
    public function plusEpisodio_updateDate($id_anime){
           
        /* Adicionando +7 dias na coluna data_semana */
        $tb_anime = table_anime::findOrFail($id_anime);
        $dataAnime = $tb_anime->data_semana;
        $newData = Carbon::parse($dataAnime)->addDay(7);
        table_anime::where('id', $id_anime)->update(['data_semana' => $newData]);
    }
    
    public function decreanime($id_anime, $id_assist){
        
        /* subtrai 7 dias na coluna assistindo */
        table_assistindo::findOrFail($id_assist)->decrement('episodio', 1);
        
        /* Adicionando exp vinculado ao usuario apos add new anime */
        $session_user = auth()->user();
        $id_user = $session_user->id;
        users::findOrFail($id_user)->decrement('exp', 1);
        
        /* subtrai 7 dias na coluna data_semana */
        $tb_anime = table_anime::findOrFail($id_anime);
        $dataAnime = $tb_anime->data_semana;
        $newData = Carbon::parse($dataAnime)->subDays(7);
        table_anime::where('id', $id_anime)->update(['data_semana' => $newData]);
        
        return redirect('/home');
    }
    
    public function plusNota($id){
        
        table_assistindo::findOrFail($id)->increment('nota', 1);
        return redirect()->route('home');
    }
    
    public function decreNota($id){
        
        table_assistindo::findOrFail($id)->decrement('nota', 1);
        return redirect()->route('home');
    }
    
    public function addranking(request $request, $id){
        
        $table_assistidos = table_assistindo::findOrFail($id);
        
        $id_ranking = $table_assistidos->id_anime;
        $ep_ranking = $table_assistidos->episodio;
        $nota_ranking = $table_assistidos->nota;
        $desc_ranking = $table_assistidos->descricao;
        $link_ranking = $table_assistidos->link;
        
        $table_ranking = new table_ranking;
        
        $table_ranking->id_anime = $id_ranking;
        $table_ranking->episodio = $ep_ranking;
        $table_ranking->nota = $nota_ranking;
        $table_ranking->descricao = $desc_ranking;
        $table_ranking->link = $link_ranking;
        $table_ranking->save();
        
        table_assistindo::findOrFail($id)->delete();
        
        $this->plusExpUser();
        
        return redirect('/home');
    }
    
    public function addcontinua(request $request, $id){
        
        $table_assistidos = table_assistindo::findOrFail($id);
        
        $id_update = $table_assistidos->id_anime;
        $ep_update = $table_assistidos->episodio;
        $nota_update = $table_assistidos->nota;
        $desc_update = $table_assistidos->descricao;
        $link_update = $table_assistidos->link;
        
        $table_continua = new table_continua;
        
        $table_continua->id_anime = $id_update;
        $table_continua->episodio = $ep_update;
        $table_continua->nota = $nota_update;
        $table_continua->dia_semana = 'null';
        $table_continua->descricao = $desc_update;
        $table_continua->link = $link_update;
        
        $table_continua->save();
        
        return redirect('/');
    }
    
    public function listAnimesParados(){
        
        $getUserData = $this->getUserInfo();

        $table_parados = AnimesParados::where('id_usuario', $getUserData['id'])->orderBy('updated_at', 'asc')->get();

        return view('pages.list-parados', compact(["table_parados", "getUserData"]));
    }
    
    public function addparados(request $request, $id){
        
        $session_user = auth()->user();
        $id_user = $session_user->id;
        
        $table_assistidos = table_assistindo::findOrFail($id);
        
        $id_update = $table_assistidos->id_anime;
        $ep_update = $table_assistidos->episodio;
        $nota_update = $table_assistidos->nota;
        $desc_update = $table_assistidos->descricao;
        $link_update = $table_assistidos->link;
        
        $animePausados = new AnimesParados;
        
        $animePausados->id_anime = $id_update;
        $animePausados->episodio = $ep_update;
        $animePausados->nota = $nota_update;
        $animePausados->descricao = $desc_update;
        $animePausados->link = $link_update;
        $animePausados->id_usuario = $id_user;
        
        $animePausados->save();
        $table_assistidos->delete();
        
        return redirect()->route('');
    }
    
    public function infoanime($id){ 
        $table_anime = table_anime::findOrFail($id);
        
        return view('pages.info-anime', ["id" => $id, "table_anime" => $table_anime]);
    }
    
    public function listadeanimes(){ 
        
        $getUserData = $this->getUserInfo();
        
        $table_animes = table_anime::all();
        
        return view('pages.lista-de-animes', ["table_animes" => $table_animes, "getUserData" => $getUserData]);
    }
    
    public function list_ranking(){
        
        $nome = "jimy";
        
        $table_rank = table_ranking::with('nome_anime')->get();
         
        return view('pages.list-ranking', compact(['nome', 'table_rank']));
    }
    
    public function plusanimec(Request $request, $id){
                               
        table_continua::findOrFail($request->id)->increment('episodio', 1);

        return redirect('/');
    }
    
    public function decreanimec(Request $request){
        
        table_continua::findOrFail($request->id)->decrement('episodio', 1);
        return redirect('/');
    }
    
    /* DEV */
    public function apache2(){
        
        $getUserData = $this->getUserInfo();
        
        return view('dev.apache2', compact(["getUserData"]));
    }
    
    public function linuxComandos(){
        
        $getUserData = $this->getUserInfo();
        
        return view('dev.linux-comandos', compact(["getUserData"]));
    }
    
    public function laravelMigrations(){
        
        $getUserData = $this->getUserInfo();
        
        return view('dev.laravel-migrations', compact(["getUserData"]));
    }
    
    public function laravelAuth(){
        
        $getUserData = $this->getUserInfo();
        
        return view('dev.laravel-auth', compact(["getUserData"]));
    }
    
    public function laravelEloquent(){
        
        $getUserData = $this->getUserInfo();
        
        return view('dev.laravel-eloquent', compact(["getUserData"]));
    }
    
    public function createProject(){
        
        $getUserData = $this->getUserInfo();
        
        return view('dev.laravel-create', compact(["getUserData"]));
    }
    
    public function createContratoE(request $request){
        
        $id_extrato = $request->id_extrato;
        $arquivo = $request->file('arquivo_extrato');
        
        if(isset($arquivo) || !empty($arquivo)){
            
            $nomeArquivo = pathinfo($arquivo->getClientOriginalName(), PATHINFO_FILENAME);
            $extensao = $arquivo->getClientOriginalExtension();
            $nomeArquivoArmazenado = $nomeArquivo . '_' . time() . '.' . $extensao;
            $arquivo->storeAs('public/documentos/contratos/', $nomeArquivoArmazenado);
            
        } else {
            return response()->json(['error' => 'O arquivo enviado não é válido.']);
        } 
        
        contratos::where('id', $id_extrato)->update(['arquivo_extrato' => $nomeArquivoArmazenado]);
        
        return redirect('/formContratos')->with(['message' => 'Post updated successfully!', 'status' => 'success']);
    }
    
}
