import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest'
import { renderHook } from '@testing-library/react'
import { useConfirmDelete } from '../useConfirmDelete'
import { Inertia } from '@inertiajs/inertia'

// Inertia.delete をモック
vi.mock('@inertiajs/inertia', () => ({
  Inertia: {
    delete: vi.fn(),
  },
}))

describe('useConfirmDelete', () => {
  let confirmSpy: ReturnType<typeof vi.spyOn>

  beforeEach(() => {
    // window.confirm をモック
    confirmSpy = vi.spyOn(window, 'confirm') as ReturnType<typeof vi.spyOn>
    vi.clearAllMocks()
  })

  afterEach(() => {
    confirmSpy.mockRestore()
  })

  describe('confirmDelete 関数', () => {
    it('confirm が true を返した場合、Inertia.delete が呼ばれる', () => {
      confirmSpy.mockReturnValue(true)
      
      const { result } = renderHook(() => useConfirmDelete())
      
      result.current.confirmDelete('テストアイテム', 'entity', '/entities/1')
      
      expect(window.confirm).toHaveBeenCalledWith('remove this entity: テストアイテム')
      expect(Inertia.delete).toHaveBeenCalledWith('/entities/1')
    })

    it('confirm が false を返した場合、Inertia.delete は呼ばれない', () => {
      confirmSpy.mockReturnValue(false)
      
      const { result } = renderHook(() => useConfirmDelete())
      
      result.current.confirmDelete('テストアイテム', 'entity', '/entities/1')
      
      expect(window.confirm).toHaveBeenCalledWith('remove this entity: テストアイテム')
      expect(Inertia.delete).not.toHaveBeenCalled()
    })

    it('正しいメッセージフォーマットで confirm を呼ぶ', () => {
      confirmSpy.mockReturnValue(true)
      
      const { result } = renderHook(() => useConfirmDelete())
      
      result.current.confirmDelete('家族の記念日', 'category', '/entities/123')
      
      expect(window.confirm).toHaveBeenCalledWith('remove this category: 家族の記念日')
    })

    it('正しいURLで Inertia.delete を呼ぶ', () => {
      confirmSpy.mockReturnValue(true)
      
      const { result } = renderHook(() => useConfirmDelete())
      
      result.current.confirmDelete('誕生日', 'anniversary', '/days/456')
      
      expect(Inertia.delete).toHaveBeenCalledWith('/days/456')
    })

    it('複数回呼び出しても独立して動作する', () => {
      const { result } = renderHook(() => useConfirmDelete())
      
      // 1回目: confirm = true
      confirmSpy.mockReturnValueOnce(true)
      result.current.confirmDelete('アイテム1', 'entity', '/entities/1')
      
      // 2回目: confirm = false
      confirmSpy.mockReturnValueOnce(false)
      result.current.confirmDelete('アイテム2', 'entity', '/entities/2')
      
      // 3回目: confirm = true
      confirmSpy.mockReturnValueOnce(true)
      result.current.confirmDelete('アイテム3', 'day', '/days/3')
      
      expect(window.confirm).toHaveBeenCalledTimes(3)
      expect(Inertia.delete).toHaveBeenCalledTimes(2) // 1回目と3回目のみ
      expect(Inertia.delete).toHaveBeenNthCalledWith(1, '/entities/1')
      expect(Inertia.delete).toHaveBeenNthCalledWith(2, '/days/3')
    })

    it('日本語文字列を含むアイテム名を正しく処理する', () => {
      confirmSpy.mockReturnValue(true)
      
      const { result } = renderHook(() => useConfirmDelete())
      
      result.current.confirmDelete('お父さんの誕生日🎂', 'anniversary', '/days/999')
      
      expect(window.confirm).toHaveBeenCalledWith('remove this anniversary: お父さんの誕生日🎂')
      expect(Inertia.delete).toHaveBeenCalledWith('/days/999')
    })

    it('空文字列のパラメータでも正常に動作する', () => {
      confirmSpy.mockReturnValue(true)
      
      const { result } = renderHook(() => useConfirmDelete())
      
      result.current.confirmDelete('', '', '/test')
      
      expect(window.confirm).toHaveBeenCalledWith('remove this : ')
      expect(Inertia.delete).toHaveBeenCalledWith('/test')
    })

    it('特殊文字を含むURLでも正常に動作する', () => {
      confirmSpy.mockReturnValue(true)
      
      const { result } = renderHook(() => useConfirmDelete())
      
      result.current.confirmDelete('テスト', 'item', '/entities/123?redirect=dashboard')
      
      expect(Inertia.delete).toHaveBeenCalledWith('/entities/123?redirect=dashboard')
    })
  })

  describe('フックの戻り値', () => {
    it('confirmDelete 関数を含むオブジェクトを返す', () => {
      const { result } = renderHook(() => useConfirmDelete())
      
      expect(result.current).toHaveProperty('confirmDelete')
      expect(typeof result.current.confirmDelete).toBe('function')
    })

    it('フックは毎回新しい confirmDelete 関数インスタンスを返す', () => {
      const { result, rerender } = renderHook(() => useConfirmDelete())
      
      const firstConfirmDelete = result.current.confirmDelete
      
      rerender()
      
      const secondConfirmDelete = result.current.confirmDelete
      
      // 実装では useCallback などを使用していないため、毎回新しい関数が作られる
      expect(firstConfirmDelete).not.toBe(secondConfirmDelete)
      expect(typeof secondConfirmDelete).toBe('function')
    })
  })
})